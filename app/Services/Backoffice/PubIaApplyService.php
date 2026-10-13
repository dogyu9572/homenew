<?php

namespace App\Services\Backoffice;

use App\Models\ProjectProvision;
use App\Services\AnthropicIaPlanner;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PubIaApplyService
{
    public function apply(ProjectProvision $provision): void
    {
        $path = Storage::disk('local')->path((string) $provision->ia_file_path);
        if (! is_file($path)) {
            $this->finish($provision, 'IA 파일을 찾지 못했습니다.');

            return;
        }

        try {
            $rows = (new IaWorkbookReader())->rows($path);
        } catch (RuntimeException $exception) {
            $this->finish($provision, $exception->getMessage());

            return;
        }

        if ($rows === []) {
            $this->finish($provision, 'IA에서 적용할 화면을 찾지 못했습니다.');

            return;
        }

        try {
            $items = (new AnthropicIaPlanner())->plan($rows);
        } catch (RuntimeException $exception) {
            $this->finish($provision, $exception->getMessage());

            return;
        }

        $items = $this->withScreenText($items, $rows);
        [$boards, $notes] = $this->split($items);
        $pubDir = $this->pubDir($provision);

        try {
            $this->prepareDatabase($provision);
            $this->chown($pubDir, $this->queueOwner());
            $this->writePubEnv($provision, $pubDir);
            $this->installPubSchema($provision, $pubDir);
        $result = $this->runApplyScript($pubDir, $boards, $provision);
            $lines = array_merge($notes, $result['notes'] ?? [], $result['created'] ?? []);
            $this->finish($provision, $lines === [] ? '적용할 게시판이 없습니다.' : implode("\n", $lines));
        } catch (Throwable $exception) {
            throw $exception instanceof RuntimeException
                ? $exception
                : new RuntimeException('퍼블 화면에 IA를 넣지 못했습니다.');
        } finally {
            if (is_dir($pubDir)) {
                try {
                    $this->restorePubOwner($provision, $pubDir);
                } catch (Throwable) {
                }
            }
        }
    }

    public function reapply(ProjectProvision $provision): void
    {
        $pubDir = $this->pubDir($provision);
        $this->chown($pubDir, $this->queueOwner());
        try {
            $this->restoreBackofficeModule($provision, $pubDir);
        } catch (Throwable $exception) {
            try {
                $this->restorePubOwner($provision, $pubDir);
            } catch (Throwable) {
            }
            throw $exception;
        }

        $this->apply($provision);
    }

    private function restoreBackofficeModule(ProjectProvision $provision, string $pubDir): void
    {
        $name = (string) $provision->project_name;
        $this->assertIdentifier($name, '프로젝트명');
        $base = rtrim((string) config('provision.base_path'), '/');
        $moduleViews = $base.'/'.$name.'/resources/views/backoffice';
        $real = realpath($moduleViews);
        $baseReal = realpath($base);
        if ($real === false || $baseReal === false || ! str_starts_with($real, $baseReal.DIRECTORY_SEPARATOR) || ! is_dir($real)) {
            throw new RuntimeException('기존 모듈의 관리자 화면을 찾지 못했습니다.');
        }

        $target = $pubDir.'/resources/views/backoffice';
        $remove = Process::path(base_path())->timeout(120)->run(['/bin/rm', '-rf', $target]);
        if (! $remove->successful()) {
            throw new RuntimeException('퍼블 관리자 화면을 지우지 못했습니다.');
        }
        $copy = Process::path(base_path())->timeout(180)->run(['sudo', '-n', 'cp', '-a', $real, $pubDir.'/resources/views/']);
        if (! $copy->successful() || ! is_dir($target)) {
            throw new RuntimeException('기존 모듈의 관리자 화면을 가져오지 못했습니다.');
        }
        $this->chown($target, $this->queueOwner());
        $this->dropGeneratedBoards($provision, $pubDir);

        $seed = Process::path($pubDir)->timeout(180)->env($this->pubEnv($provision))->run([
            'php', 'artisan', 'db:seed', '--force', '--class=Database\\Seeders\\AdminMenuSeeder',
        ]);
        if (! $seed->successful()) {
            throw new RuntimeException('퍼블 관리자 메뉴를 되돌리지 못했습니다.');
        }
    }

    private function dropGeneratedBoards(ProjectProvision $provision, string $pubDir): void
    {
        $database = 'pub'.(string) $provision->project_name;
        $raw = $this->mysql('SELECT slug FROM `'.$database.'`.boards');
        $slugs = $raw === '' ? [] : preg_split('/\r\n|\r|\n/', $raw);
        foreach ($slugs ?: [] as $slug) {
            $slug = trim((string) $slug);
            if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                continue;
            }
            $this->mysql('DROP TABLE IF EXISTS `'.$database.'`.`board_'.$slug.'`', '만든 게시판 테이블을 지우지 못했습니다.');
        }
        $this->mysql('DELETE FROM `'.$database.'`.boards', '만든 게시판을 지우지 못했습니다.');

        $moduleMigrations = dirname($pubDir).'/'.(string) $provision->project_name.'/database/migrations';
        foreach (glob($pubDir.'/database/migrations/*_create_board_*_table.php') ?: [] as $file) {
            if (! is_file($moduleMigrations.'/'.basename($file))) {
                unlink($file);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{0: list<array<string, mixed>>, 1: list<string>}
     */
    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<array<string, string>>  $rows
     * @return list<array<string, mixed>>
     */
    private function withScreenText(array $items, array $rows): array
    {
        $lists = [];
        $forms = [];
        foreach ($rows as $row) {
            $label = $this->screenLabel((string) ($row['depth1'] ?? ''), (string) ($row['depth2'] ?? ''));
            if ($label === '') {
                continue;
            }
            $text = trim((string) ($row['description'] ?? ''));
            $isList = ($row['depth3'] ?? '') === '리스트' || str_contains($text, '리스트:');
            if ($isList && ! isset($lists[$label])) {
                $lists[$label] = $text;
            } elseif (! $isList) {
                $forms[$label] = trim(($forms[$label] ?? '')."\n".$text);
            }
        }

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            $keys = array_filter([
                $this->screenLabel((string) ($item['depth1'] ?? ''), (string) ($item['depth2'] ?? '')),
                trim((string) ($item['depth2'] ?? '')),
                trim((string) ($item['name'] ?? '')),
            ]);
            foreach ($keys as $key) {
                if (! array_key_exists($key, $lists) && ! array_key_exists($key, $forms)) {
                    continue;
                }
                $items[$index]['list_text'] = $lists[$key] ?? '';
                $items[$index]['form_text'] = $forms[$key] ?? '';
                $items[$index]['screen_matched'] = true;
                break;
            }
        }

        return $items;
    }

    private function screenLabel(string $depth1, string $depth2): string
    {
        $depth1 = trim($depth1);
        $depth2 = trim($depth2);
        if ($depth1 !== '' && $depth2 !== '' && preg_match('/등록|상세|환불/u', $depth2)) {
            return $depth1;
        }
        $types = ['리스트', '목록', '상세', '등록', '수정', '삭제', '등록 / 수정', '등록/수정', '삭제/선택삭제'];
        if ($depth2 !== '' && ! in_array($depth2, $types, true)) {
            return $depth2;
        }
        if ($depth2 === '' || in_array($depth2, ['리스트', '목록'], true)) {
            return $depth1;
        }

        return trim($depth1.' '.$depth2);
    }

    private function split(array $items): array
    {
        $boards = [];
        $notes = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? $item['depth2'] ?? $item['depth1'] ?? ''));
            if ($name === '') {
                continue;
            }

            $item['kind'] = 'board';
            $boards[] = $item;
            $note = trim((string) ($item['note'] ?? ''));
            if ($note !== '') {
                $notes[] = $name.': '.$note;
            }
        }

        return [$boards, $notes];
    }

    private function pubDir(ProjectProvision $provision): string
    {
        $name = (string) $provision->project_name;
        $this->assertIdentifier($name, '프로젝트명');
        $base = rtrim((string) config('provision.base_path'), '/');
        $dir = $base.'/pub'.$name;
        $real = realpath($dir);
        if ($real === false || $real !== $dir || ! is_file($dir.'/artisan')) {
            throw new RuntimeException('퍼블 디렉터리를 찾지 못했습니다.');
        }

        return $dir;
    }

    private function prepareDatabase(ProjectProvision $provision): void
    {
        $database = 'pub'.(string) $provision->project_name;
        $user = (string) $provision->db_username;
        $this->assertIdentifier((string) $provision->project_name, '프로젝트명');
        $this->assertIdentifier($user, 'DB 아이디');

        $sql = 'CREATE DATABASE IF NOT EXISTS `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
            .'GRANT ALL PRIVILEGES ON `'.$database.'`.* TO \''.$user.'\'@\'127.0.0.1\';'
            .'GRANT ALL PRIVILEGES ON `'.$database.'`.* TO \''.$user.'\'@\'localhost\';'
            .'GRANT ALL PRIVILEGES ON `'.$database.'`.* TO \''.$user.'\'@\'%\';'
            .'FLUSH PRIVILEGES;';

        $this->mysql($sql);
    }

    private function mysql(string $sql, string $failure = '퍼블 데이터베이스를 만들지 못했습니다.'): string
    {
        $adminUser = (string) config('provision.db_admin_user');
        $adminPassword = (string) config('provision.db_admin_password');
        if ($adminUser === '' || $adminPassword === '') {
            throw new RuntimeException('데이터베이스 관리 설정이 없습니다.');
        }

        $cnf = storage_path('app/ia-mysql-'.bin2hex(random_bytes(8)).'.cnf');
        file_put_contents($cnf, "[client]\nuser=\"".$this->quote($adminUser)."\"\npassword=\"".$this->quote($adminPassword)."\"\nhost=127.0.0.1\nprotocol=tcp\n");
        chmod($cnf, 0600);

        try {
            $process = Process::path(base_path())->timeout(60)->run(['mysql', '--defaults-extra-file='.$cnf, '-N', '-e', $sql]);
            if (! $process->successful()) {
                throw new RuntimeException($failure);
            }

            return trim($process->output());
        } finally {
            if (is_file($cnf)) {
                @unlink($cnf);
            }
        }
    }

    private function writePubEnv(ProjectProvision $provision, string $pubDir): void
    {
        $envPath = $pubDir.'/.env';
        $env = is_file($envPath) ? (string) file_get_contents($envPath) : '';
        $map = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'pub'.$provision->project_name,
            'DB_USERNAME' => (string) $provision->db_username,
            'DB_PASSWORD' => (string) $provision->db_password,
        ];
        foreach ($map as $key => $value) {
            $line = $key.'="'.$this->quote($value).'"';
            $pattern = '/^'.preg_quote($key, '/').'=.*/m';
            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $line, $env, 1) ?? $env;
            } else {
                $env .= PHP_EOL.$line;
            }
        }
        file_put_contents($envPath, $env);
    }

    private function installPubSchema(ProjectProvision $provision, string $pubDir): void
    {
        $migrate = Process::path($pubDir)->timeout(600)->env($this->pubEnv($provision))->run(['php', 'artisan', 'migrate', '--force']);
        if (! $migrate->successful()) {
            throw new RuntimeException('퍼블 기본 테이블을 만들지 못했습니다.');
        }

        $database = 'pub'.(string) $provision->project_name;
        $count = trim($this->mysql(
            'SELECT COUNT(*) FROM `'.$database.'`.`users`',
            '퍼블 기본 테이블을 확인하지 못했습니다.'
        ));
        if ($count !== '0') {
            return;
        }

        foreach ([
            'Database\\Seeders\\BoardSkinSeeder',
            'Database\\Seeders\\UserSeeder',
            'Database\\Seeders\\AdminMenuSeeder',
        ] as $class) {
            $seed = Process::path($pubDir)->timeout(180)->env($this->pubEnv($provision))->run(['php', 'artisan', 'db:seed', '--force', '--class='.$class]);
            if (! $seed->successful()) {
                throw new RuntimeException('퍼블 기본 화면 데이터를 넣지 못했습니다.');
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $boards
     * @return array<string, mixed>
     */
    private function runApplyScript(string $pubDir, array $boards, ProjectProvision $provision): array
    {
        $payload = storage_path('app/ia-provisions/plan-'.bin2hex(random_bytes(8)).'.json');
        if (! is_dir(dirname($payload))) {
            mkdir(dirname($payload), 0700, true);
        }
        file_put_contents($payload, json_encode($boards, JSON_UNESCAPED_UNICODE));
        chmod($payload, 0600);

        try {
            $process = Process::path($pubDir)->timeout(300)->env(array_merge($this->pubEnv($provision), [
                'PUB_DIR' => $pubDir,
                'IA_PAYLOAD' => $payload,
            ]))->run(['php', base_path('scripts/apply-pub-ia.php')]);
            if (! $process->successful()) {
                throw new RuntimeException('퍼블 게시판을 만들지 못했습니다.');
            }

            $decoded = json_decode($process->output(), true);

            return is_array($decoded) ? $decoded : ['created' => [], 'notes' => []];
        } finally {
            if (is_file($payload)) {
                @unlink($payload);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function pubEnv(ProjectProvision $provision): array
    {
        return [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'pub'.$provision->project_name,
            'DB_USERNAME' => (string) $provision->db_username,
            'DB_PASSWORD' => (string) $provision->db_password,
        ];
    }

    private function queueOwner(): string
    {
        $name = trim((string) Process::path(base_path())->run(['whoami'])->output());
        $name = str_contains($name, '\\') ? (string) substr($name, strrpos($name, '\\') + 1) : $name;
        $group = (string) config('provision.web_user', 'www-data');
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
            throw new RuntimeException('실행 계정을 확인하지 못했습니다.');
        }

        return $name.':'.$group;
    }

    private function restorePubOwner(ProjectProvision $provision, string $pubDir): void
    {
        $user = 'pub'.(string) $provision->ftp_username;
        $this->assertIdentifier((string) $provision->ftp_username, 'FTP 아이디');
        $this->chown($pubDir, $user.':'.(string) config('provision.web_user', 'www-data'));
        Process::path(base_path())->timeout(120)->run(['sudo', '-n', 'chmod', '775', $pubDir.'/artisan']);
        Process::path(base_path())->timeout(120)->run(['sudo', '-n', 'chmod', '-R', 'ug+rwX', $pubDir.'/storage', $pubDir.'/bootstrap/cache']);
    }

    private function chown(string $dir, string $owner): void
    {
        $process = Process::path(base_path())->timeout(180)->run(['sudo', '-n', 'chown', '-R', $owner, $dir]);
        if (! $process->successful()) {
            throw new RuntimeException('퍼블 디렉터리 소유권을 바꾸지 못했습니다.');
        }
    }

    private function assertIdentifier(string $value, string $label): void
    {
        if (! preg_match('/^[a-z][a-z0-9-]{0,31}$/', $value)) {
            throw new RuntimeException($label.'이 올바르지 않습니다.');
        }
    }

    private function quote(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }

    private function finish(ProjectProvision $provision, string $message): void
    {
        if (strlen($message) > 8000) {
            $message = substr($message, 0, 8000);
        }

        $provision->ia_apply_note = $message;
        $provision->log = trim((string) $provision->log."\n".$message);
        $provision->save();
    }
}
