<?php

namespace App\Services\Backoffice;

use App\Jobs\ApplyProvisionIaJob;
use App\Models\ProjectProvision;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

class ProjectProvisionService
{
    private string $lineBuffer = '';

    /** @var list<string> */
    private array $secrets = [];

    public function run(ProjectProvision $provision): void
    {
        $provision->status = ProjectProvision::STATUS_RUNNING;
        $provision->step = 'prepare';
        $provision->save();

        $this->secrets = array_values(array_filter([
            (string) $provision->root_password,
            (string) $provision->ftp_password,
            (string) $provision->db_password,
            (string) config('provision.db_admin_password'),
            (string) config('provision.github_token'),
            (string) config('provision.github_webhook_secret'),
            $provision->stack === ProjectProvision::STACK_JAVA ? 'homepagekorea' : '',
        ]));

        $missing = $this->missingConfig($provision);
        if ($missing !== []) {
            $this->fail($provision, '서버 설정이 없습니다: '.implode(', ', $missing));

            return;
        }

        $script = $provision->stack === ProjectProvision::STACK_JAVA
            ? (string) config('provision.java_script')
            : (string) config('provision.script');
        if (! is_file($script)) {
            $this->fail($provision, '빌드 스크립트를 찾을 수 없습니다.');

            return;
        }

        $credsFile = storage_path('app/provision-'.$provision->id.'.env');

        try {
            $this->writeCredsFile($provision, $credsFile);

            $process = Process::timeout(1800)
                ->env($this->processEnvironment())
                ->run(['bash', $script, $credsFile], function (string $type, string $output) use ($provision): void {
                    $this->consumeOutput($provision->id, $output);
                });

            $this->flushBuffer($provision->id);

            if (! $process->successful()) {
                $this->fail($provision, '빌드 스크립트가 실패했습니다. (종료 코드 '.$process->exitCode().')');

                return;
            }

            $provision->refresh();
            $provision->status = ProjectProvision::STATUS_SUCCESS;
            $provision->step = 'done';
            $provision->save();

            if (is_string($provision->ia_file_path) && $provision->ia_file_path !== '') {
                ApplyProvisionIaJob::dispatch($provision->id);
            }
        } catch (Throwable $exception) {
            Log::error('프로젝트 빌드 실패', [
                'provision_id' => $provision->id,
                'message' => $exception->getMessage(),
            ]);
            $this->fail($provision, '빌드 중 오류가 발생했습니다.');
        } finally {
            if (is_file($credsFile)) {
                @unlink($credsFile);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function missingConfig(ProjectProvision $provision): array
    {
        $required = [
            'PROVISION_DB_ADMIN_PASSWORD' => config('provision.db_admin_password'),
            'GITHUB_TOKEN' => config('provision.github_token'),
            'PROVISION_BASE_PATH' => config('provision.base_path'),
            'PROVISION_DOMAIN_SUFFIX' => config('provision.domain_suffix'),
        ];
        if ($provision->stack === ProjectProvision::STACK_JAVA) {
            $required['GITHUB_WEBHOOK_SECRET'] = config('provision.github_webhook_secret');
            $required['PROVISION_JAVA_TEMPLATE_MIRROR'] = config('provision.java_template_mirror');
            $required['PROVISION_JAVA_TEMPLATE_SOURCE'] = config('provision.java_template_source');
            $required['PROVISION_JAVA_TEMPLATE_DATABASE'] = config('provision.java_template_database');
        } else {
            $required['PROVISION_TEMPLATE_MIRROR'] = config('provision.template_mirror');
        }

        $missing = [];
        foreach ($required as $key => $value) {
            if (! is_string($value) || $value === '') {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    private function writeCredsFile(ProjectProvision $provision, string $path): void
    {
        $values = [
            'PROJECT_NAME' => $provision->project_name,
            'FTP_USERNAME' => $provision->ftp_username,
            'FTP_PASSWORD' => $provision->ftp_password,
            'DB_USERNAME' => $provision->db_username,
            'DB_PASSWORD' => $provision->db_password,
            'DB_DATABASE' => $provision->db_database,
            'PROVISION_DB_ADMIN_USER' => (string) config('provision.db_admin_user'),
            'PROVISION_DB_ADMIN_PASSWORD' => (string) config('provision.db_admin_password'),
            'GITHUB_TOKEN' => (string) config('provision.github_token'),
            'GITHUB_ORG' => (string) config('provision.github_org'),
            'GITHUB_WEBHOOK_SECRET' => (string) config('provision.github_webhook_secret'),
            'TEMPLATE_MIRROR' => $provision->stack === ProjectProvision::STACK_JAVA
                ? (string) config('provision.java_template_mirror')
                : (string) config('provision.template_mirror'),
            'JAVA_TEMPLATE_SOURCE' => (string) config('provision.java_template_source'),
            'JAVA_TEMPLATE_DATABASE' => (string) config('provision.java_template_database'),
            'JAVA_ADM_JAR' => (string) config('provision.java_adm_jar'),
            'JAVA_USER_JAR' => (string) config('provision.java_user_jar'),
            'BASE_PATH' => (string) config('provision.base_path'),
            'DOMAIN_SUFFIX' => (string) config('provision.domain_suffix'),
            'WEB_USER' => (string) config('provision.web_user'),
            'PROVISION_PHP_SOCKET' => (string) config('provision.php_socket'),
            'PROVISION_NGINX_PORT' => (string) config('provision.nginx_port'),
            'PROVISION_URL_SCHEME' => (string) config('provision.url_scheme'),
            'PROVISION_SKIP_CERTBOT' => config('provision.skip_certbot') ? '1' : '0',
            'PROVISION_SSL_FULLCHAIN' => (string) config('provision.ssl_fullchain'),
            'PROVISION_SSL_PRIVKEY' => (string) config('provision.ssl_privkey'),
            'PROVISION_CERTBOT_EMAIL' => (string) config('provision.certbot_email'),
        ];

        $lines = [];
        foreach ($values as $key => $value) {
            $lines[] = $key.'='.$this->shellQuote($value);
        }

        file_put_contents($path, implode("\n", $lines)."\n");
        @chmod($path, 0600);
    }

    private function shellQuote(string $value): string
    {
        return "'".str_replace("'", "'\\''", $value)."'";
    }

    /**
     * @return array<string, string>
     */
    private function processEnvironment(): array
    {
        $env = [];
        foreach (['PATH', 'HOME', 'LANG', 'LC_ALL', 'USER', 'LOGNAME', 'SHELL', 'TMPDIR', 'TEMP', 'TMP'] as $key) {
            $value = getenv($key);
            if (is_string($value) && $value !== '') {
                $env[$key] = $value;
            }
        }

        if (! isset($env['PATH'])) {
            $env['PATH'] = '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin';
        } else {
            $env['PATH'] .= ':/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin';
        }
        if (! isset($env['HOME'])) {
            $env['HOME'] = '/tmp';
        }
        if (! isset($env['LANG'])) {
            $env['LANG'] = 'C.UTF-8';
        }

        $env['GIT_TERMINAL_PROMPT'] = '0';

        return $env;
    }

    private function consumeOutput(int $provisionId, string $chunk): void
    {
        $this->lineBuffer .= $chunk;

        while (($position = strpos($this->lineBuffer, "\n")) !== false) {
            $line = substr($this->lineBuffer, 0, $position);
            $this->lineBuffer = substr($this->lineBuffer, $position + 1);
            $this->handleLine($provisionId, $line);
        }
    }

    private function flushBuffer(int $provisionId): void
    {
        if ($this->lineBuffer === '') {
            return;
        }

        $this->handleLine($provisionId, $this->lineBuffer);
        $this->lineBuffer = '';
    }

    private function handleLine(int $provisionId, string $line): void
    {
        $line = rtrim($line, "\r");
        if ($line === '' || $this->containsSecret($line)) {
            return;
        }

        $provision = ProjectProvision::query()->find($provisionId);
        if (! $provision) {
            return;
        }

        if (str_starts_with($line, '[[STEP]] ')) {
            $provision->step = trim(substr($line, strlen('[[STEP]] ')));
            $provision->save();

            return;
        }

        if (str_starts_with($line, '[[RESULT_URL]] ')) {
            $provision->site_url = trim(substr($line, strlen('[[RESULT_URL]] ')));
            $provision->save();

            return;
        }

        if (str_starts_with($line, '[[RESULT_PUB_URL]] ')) {
            $provision->pub_site_url = trim(substr($line, strlen('[[RESULT_PUB_URL]] ')));
            $provision->save();

            return;
        }

        if (str_starts_with($line, '[[RESULT_GIT]] ')) {
            $provision->git_url = trim(substr($line, strlen('[[RESULT_GIT]] ')));
            $provision->save();
            app(ProjectDeployService::class)->createFromProvision($provision);

            return;
        }

        $provision->log = trim(($provision->log ?? '')."\n".$line);
        $provision->save();
    }

    private function containsSecret(string $line): bool
    {
        foreach ($this->secrets as $secret) {
            if ($secret !== '' && str_contains($line, $secret)) {
                return true;
            }
        }

        return false;
    }

    private function fail(ProjectProvision $provision, string $message): void
    {
        $this->flushBuffer($provision->id);
        $provision->refresh();
        $provision->status = ProjectProvision::STATUS_FAILED;
        $provision->log = trim(($provision->log ?? '')."\n".$message);
        $provision->save();
    }

    /**
     * @return list<string>
     */
    public function serverDirectories(): array
    {
        $basePath = $this->basePath();
        if (! is_dir($basePath)) {
            return [];
        }

        $names = [];
        foreach (scandir($basePath) ?: [] as $name) {
            if (! $this->isDeletableName($name) || ! is_dir($basePath.'/'.$name) || is_link($basePath.'/'.$name)) {
                continue;
            }
            $names[] = $name;
        }

        sort($names, SORT_STRING);

        return $names;
    }

    /**
     * @return array<string, int>
     */
    public function directorySizes(): array
    {
        $cacheFile = storage_path('app/project-provision-sizes.json');
        $duFile = storage_path('app/project-provision-sizes.du');
        if (is_file($duFile)) {
            $sizes = $this->parseDuOutput((string) file_get_contents($duFile));
            if ($sizes !== []) {
                $this->writeSizeCache($cacheFile, $sizes);
                @unlink($duFile);

                return $sizes;
            }
        }

        $cached = $this->readSizeCache($cacheFile);

        return $cached['sizes'] ?? [];
    }

    public function refreshDirectorySizes(): void
    {
        $basePath = $this->basePath();
        if (! preg_match('#^/[A-Za-z0-9/_.-]+$#', $basePath)) {
            throw new RuntimeException('용량을 계산하지 못했습니다.');
        }

        $process = Process::timeout(90)->run(['bash', '-c', 'du -sb '.$basePath.'/*']);
        $sizes = $this->parseDuOutput($process->output());
        if ($sizes === []) {
            throw new RuntimeException('용량을 계산하지 못했습니다.');
        }

        $this->writeSizeCache(storage_path('app/project-provision-sizes.json'), $sizes);
    }

    /**
     * @return array<string, int|string>
     */
    public function serverStats(): array
    {
        return $this->measureServerStats();
    }

    /**
     * @return array<string, int|string>
     */
    private function measureServerStats(): array
    {
        $basePath = $this->basePath();
        $total = (int) disk_total_space($basePath);
        $free = (int) disk_free_space($basePath);
        $used = max(0, $total - $free);
        $memory = $this->memoryStats();
        $load = sys_getloadavg() ?: [0, 0, 0];
        $rates = $this->ioRates();

        return [
            'disk_total' => $this->formatBytes($total),
            'disk_used' => $this->formatBytes($used),
            'disk_free' => $this->formatBytes($free),
            'disk_percent' => $total > 0 ? (int) round(($used / $total) * 100) : 0,
            'memory_total' => $this->formatBytes($memory['total']),
            'memory_free' => $this->formatBytes($memory['free']),
            'load' => number_format((float) $load[0], 2).' / '.number_format((float) $load[1], 2).' / '.number_format((float) $load[2], 2),
            'disk_read' => $rates['disk_read'],
            'disk_write' => $rates['disk_write'],
            'net_rx' => $rates['net_rx'],
            'net_tx' => $rates['net_tx'],
        ];
    }

    /**
     * @return array{total: int, free: int}
     */
    private function memoryStats(): array
    {
        $total = 0;
        $free = 0;
        $content = @file_get_contents('/proc/meminfo') ?: '';
        if (preg_match('/MemTotal:\s+(\d+)/', $content, $matches)) {
            $total = (int) $matches[1] * 1024;
        }
        if (preg_match('/MemAvailable:\s+(\d+)/', $content, $matches)) {
            $free = (int) $matches[1] * 1024;
        }

        return ['total' => $total, 'free' => $free];
    }

    /**
     * @return array{disk_read: string, disk_write: string, net_rx: string, net_tx: string}
     */
    private function ioRates(): array
    {
        $empty = [
            'disk_read' => '-',
            'disk_write' => '-',
            'net_rx' => '-',
            'net_tx' => '-',
        ];
        $current = $this->ioCounters();
        $file = storage_path('app/project-provision-io.json');
        $previous = null;
        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded)) {
                $previous = $decoded;
            }
        }
        file_put_contents($file, json_encode($current));
        if (! is_array($previous) || ! isset($previous['time'])) {
            return $empty;
        }

        $seconds = $current['time'] - (int) $previous['time'];
        if ($seconds < 1) {
            return $empty;
        }

        return [
            'disk_read' => $this->formatBytes((int) (max(0, $current['read'] - (int) ($previous['read'] ?? 0)) / $seconds)).'/s',
            'disk_write' => $this->formatBytes((int) (max(0, $current['write'] - (int) ($previous['write'] ?? 0)) / $seconds)).'/s',
            'net_rx' => $this->formatBytes((int) (max(0, $current['rx'] - (int) ($previous['rx'] ?? 0)) / $seconds)).'/s',
            'net_tx' => $this->formatBytes((int) (max(0, $current['tx'] - (int) ($previous['tx'] ?? 0)) / $seconds)).'/s',
        ];
    }

    /**
     * @return array{time: int, read: int, write: int, rx: int, tx: int}
     */
    private function ioCounters(): array
    {
        $read = 0;
        $write = 0;
        $diskstats = @file_get_contents('/proc/diskstats') ?: '';
        foreach (preg_split("/\r\n|\n|\r/", $diskstats) ?: [] as $line) {
            $parts = preg_split('/\s+/', trim($line)) ?: [];
            if (count($parts) < 10 || ! preg_match('/^(nvme\d+n\d+|sd[a-z]|vd[a-z]|xvd[a-z])$/', $parts[2])) {
                continue;
            }
            $read += (int) $parts[5] * 512;
            $write += (int) $parts[9] * 512;
        }

        $rx = 0;
        $tx = 0;
        $netdev = @file_get_contents('/proc/net/dev') ?: '';
        foreach (preg_split("/\r\n|\n|\r/", $netdev) ?: [] as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$name, $values] = array_map('trim', explode(':', $line, 2));
            if ($name === '' || $name === 'lo') {
                continue;
            }
            $parts = preg_split('/\s+/', trim($values)) ?: [];
            $rx += (int) ($parts[0] ?? 0);
            $tx += (int) ($parts[8] ?? 0);
        }

        return [
            'time' => time(),
            'read' => $read,
            'write' => $write,
            'rx' => $rx,
            'tx' => $tx,
        ];
    }

    /**
     * @return array{time: int, sizes: array<string, int>}|null
     */
    private function readSizeCache(string $cacheFile): ?array
    {
        if (! is_file($cacheFile)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($cacheFile), true);
        if (! is_array($decoded) || ! isset($decoded['time'], $decoded['sizes']) || ! is_array($decoded['sizes'])) {
            return null;
        }

        $sizes = [];
        foreach ($decoded['sizes'] as $name => $bytes) {
            $sizes[(string) $name] = (int) $bytes;
        }

        return [
            'time' => (int) $decoded['time'],
            'sizes' => $sizes,
        ];
    }

    /**
     * @param  array<string, int>  $sizes
     */
    private function writeSizeCache(string $cacheFile, array $sizes): void
    {
        file_put_contents($cacheFile, json_encode([
            'time' => time(),
            'sizes' => $sizes,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return array<string, int>
     */
    private function parseDuOutput(string $output): array
    {
        $sizes = [];
        foreach (preg_split("/\r\n|\n|\r/", $output) ?: [] as $line) {
            if (! preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
                continue;
            }
            $sizes[basename($matches[2])] = (int) $matches[1];
        }

        return $sizes;
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1).'GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 0).'MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).'KB';
        }

        return $bytes.'B';
    }

    public function deleteProvision(ProjectProvision $provision): void
    {
        if (in_array($provision->status, [ProjectProvision::STATUS_QUEUED, ProjectProvision::STATUS_RUNNING], true)) {
            throw new RuntimeException('생성이 진행 중인 프로젝트는 삭제할 수 없습니다.');
        }

        if ($this->isKeptSite($provision->project_name)) {
            throw new RuntimeException('이 프로젝트는 삭제할 수 없습니다.');
        }

        $metaFile = storage_path('app/remove-site-meta-'.uniqid('', true).'.json');
        file_put_contents($metaFile, json_encode($this->provisionMeta($provision), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        @chmod($metaFile, 0600);

        try {
            if ($provision->stack === ProjectProvision::STACK_JAVA) {
                $this->stopJava($provision);
                $this->removeSites(
                    [$provision->project_name],
                    [$provision->db_database],
                    [$provision->db_username],
                    [$provision->ftp_username],
                    $metaFile
                );
            } else {
                $this->removeSites(
                    [$provision->project_name, 'pub'.$provision->project_name],
                    [$provision->db_database],
                    [$provision->db_username],
                    [$provision->ftp_username, 'pub'.$provision->ftp_username, $provision->project_name],
                    $metaFile
                );
            }
        } finally {
            if (is_file($metaFile)) {
                @unlink($metaFile);
            }
        }

        $provision->delete();
    }

    public function deleteDirectory(string $name): void
    {
        if (! $this->isDeletableName($name) || $this->isKeptSite($name)) {
            throw new RuntimeException('삭제할 수 없는 디렉터리입니다.');
        }

        $this->removeSites([$name, 'pub'.$name], [$name], [$name], [$name]);
    }

    private function stopJava(ProjectProvision $provision): void
    {
        $name = (string) $provision->project_name;
        if (! preg_match('/^[a-z][a-z0-9-]{1,30}$/', $name) || $name === 'hk-java') {
            throw new RuntimeException('자바 프로젝트를 정리할 수 없습니다.');
        }

        $script = '/usr/local/sbin/hk-java-activate.sh';
        if (! is_file($script)) {
            throw new RuntimeException('자바 정리 스크립트가 없습니다.');
        }

        $process = Process::timeout(120)->run(['sudo', '-n', '/bin/bash', $script, 'stop', $name]);
        if (! $process->successful()) {
            throw new RuntimeException('자바 서비스를 끄지 못했습니다.');
        }
    }

    /**
     * @param  list<string>  $directories
     * @param  list<string>  $databases
     * @param  list<string>  $dbUsers
     * @param  list<string>  $linuxUsers
     */
    private function removeSites(array $directories, array $databases, array $dbUsers, array $linuxUsers, string $metaFile = ''): void
    {
        $directories = $this->filterNames($directories);
        $databases = $this->filterNames($databases);
        $dbUsers = $this->filterNames($dbUsers);
        $linuxUsers = $this->filterNames($linuxUsers);

        if ($directories === []) {
            throw new RuntimeException('삭제할 디렉터리가 없습니다.');
        }

        $script = '/usr/local/sbin/hk-remove-site.sh';
        if (! is_file($script)) {
            throw new RuntimeException('삭제 스크립트를 찾을 수 없습니다.');
        }

        $credsFile = storage_path('app/remove-site-'.uniqid('', true).'.env');
        $this->writeRemoveCreds($credsFile, $directories, $databases, $dbUsers, $linuxUsers, $metaFile);

        try {
            $process = Process::timeout(600)
                ->env($this->processEnvironment())
                ->run(['sudo', '-n', '/bin/bash', $script, $credsFile]);

            if (! $process->successful()) {
                $output = trim($process->errorOutput()."\n".$process->output());
                Log::error('프로젝트 서버 자료 삭제 실패', [
                    'output' => $output,
                ]);
                if (str_contains($output, '백업')) {
                    throw new RuntimeException('백업을 만들지 못해 삭제하지 않았습니다.');
                }
                throw new RuntimeException('서버 자료 삭제에 실패했습니다.');
            }
        } finally {
            if (is_file($credsFile)) {
                @unlink($credsFile);
            }
        }
    }

    /**
     * @return list<object{file: string, project: string, size: int, created_at: int}>
     */
    public function backupArchives(): array
    {
        $dir = $this->backupDirectory();
        if (! is_dir($dir)) {
            return [];
        }

        $items = [];
        foreach (scandir($dir) ?: [] as $file) {
            if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\.tar\.gz$/', $file)) {
                continue;
            }
            $path = $dir.'/'.$file;
            if (! is_file($path)) {
                continue;
            }
            $items[] = (object) [
                'file' => $file,
                'project' => preg_match('/^(.+)-\d{14}(?:-\d+)?\.tar\.gz$/', $file, $matches) ? $matches[1] : $file,
                'size' => (int) filesize($path),
                'created_at' => (int) filemtime($path),
            ];
        }

        usort($items, fn ($left, $right) => $right->created_at <=> $left->created_at);

        return $items;
    }

    public function restoreBackup(string $file): void
    {
        $meta = $this->readProvisionMeta($file);
        $this->runArchiveCommand('restore', $file, '프로젝트 백업 복구 실패', function (string $output): string {
            if (str_contains($output, '이미 폴더가 있어')) {
                return '같은 이름의 폴더가 있어 복구하지 않았습니다.';
            }

            return '백업 복구에 실패했습니다.';
        });
        if ($meta !== null) {
            $this->restoreProvisionRecord($meta);
        }
    }

    public function deleteBackup(string $file): void
    {
        $this->runArchiveCommand('delete-backup', $file, '프로젝트 백업 삭제 실패', function (): string {
            return '백업 삭제에 실패했습니다.';
        });
    }

    private function runArchiveCommand(string $mode, string $file, string $logLabel, callable $failureMessage): void
    {
        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\.tar\.gz$/', $file)) {
            throw new RuntimeException('백업 파일명이 올바르지 않습니다.');
        }

        $script = '/usr/local/sbin/hk-remove-site.sh';
        if (! is_file($script)) {
            throw new RuntimeException('백업 스크립트를 찾을 수 없습니다.');
        }

        $credsFile = storage_path('app/remove-site-'.uniqid('', true).'.env');
        $this->writeCreds($credsFile, [
            'MODE' => $mode,
            'ARCHIVE' => $file,
            'BASE_PATH' => $this->basePath(),
            'PROVISION_DB_ADMIN_USER' => (string) config('provision.db_admin_user'),
            'PROVISION_DB_ADMIN_PASSWORD' => (string) config('provision.db_admin_password'),
        ]);

        try {
            $process = Process::timeout(1800)
                ->env($this->processEnvironment())
                ->run(['sudo', '-n', '/bin/bash', $script, $credsFile]);

            if (! $process->successful()) {
                $output = trim($process->errorOutput()."\n".$process->output());
                Log::error($logLabel, [
                    'output' => $output,
                ]);
                throw new RuntimeException($failureMessage($output));
            }
        } finally {
            if (is_file($credsFile)) {
                @unlink($credsFile);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function provisionMeta(ProjectProvision $provision): array
    {
        return [
            'customer_name' => $provision->customer_name,
            'project_name' => $provision->project_name,
            'stack' => $provision->stack,
            'ftp_username' => $provision->ftp_username,
            'ftp_password' => $provision->ftp_password,
            'db_username' => $provision->db_username,
            'db_password' => $provision->db_password,
            'db_database' => $provision->db_database,
            'status' => $provision->status,
            'step' => $provision->step,
            'log' => $provision->log,
            'site_url' => $provision->site_url,
            'pub_site_url' => $provision->pub_site_url,
            'git_url' => $provision->git_url,
            'created_by' => $provision->created_by,
            'created_at' => $provision->created_at?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readProvisionMeta(string $file): ?array
    {
        $path = $this->backupDirectory().'/'.$file;
        if (! is_file($path)) {
            return null;
        }

        foreach (['./meta/provision.json', 'meta/provision.json'] as $member) {
            $process = Process::timeout(60)->run(['tar', '-xOzf', $path, $member]);
            if (! $process->successful()) {
                continue;
            }
            $meta = json_decode($process->output(), true);
            if (is_array($meta) && isset($meta['project_name'])) {
                return $meta;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function restoreProvisionRecord(array $meta): void
    {
        $name = (string) ($meta['project_name'] ?? '');
        if (! $this->isDeletableName($name)) {
            return;
        }

        $provision = ProjectProvision::query()->firstOrNew(['project_name' => $name]);
        $provision->fill([
            'customer_name' => $meta['customer_name'] ?? null,
            'stack' => (string) ($meta['stack'] ?? ProjectProvision::STACK_LARAVEL),
            'ftp_username' => (string) ($meta['ftp_username'] ?? ''),
            'ftp_password' => (string) ($meta['ftp_password'] ?? ''),
            'db_username' => (string) ($meta['db_username'] ?? ''),
            'db_password' => (string) ($meta['db_password'] ?? ''),
            'db_database' => (string) ($meta['db_database'] ?? $name),
            'status' => (string) ($meta['status'] ?? ProjectProvision::STATUS_SUCCESS),
            'step' => $meta['step'] ?? null,
            'log' => $meta['log'] ?? null,
            'site_url' => $meta['site_url'] ?? null,
            'pub_site_url' => $meta['pub_site_url'] ?? null,
            'git_url' => $meta['git_url'] ?? null,
            'created_by' => $meta['created_by'] ?? null,
        ]);
        if (! empty($meta['created_at'])) {
            $provision->created_at = $meta['created_at'];
        }
        $provision->save();
    }

    private function backupDirectory(): string
    {
        return '/var/backups/hk-sites';
    }

    /**
     * @param  list<string>  $directories
     * @param  list<string>  $databases
     * @param  list<string>  $dbUsers
     * @param  list<string>  $linuxUsers
     */
    private function writeRemoveCreds(string $path, array $directories, array $databases, array $dbUsers, array $linuxUsers, string $metaFile = ''): void
    {
        $values = [
            'MODE' => 'delete',
            'BASE_PATH' => $this->basePath(),
            'DIRECTORIES' => implode(' ', $directories),
            'DATABASES' => implode(' ', $databases),
            'DB_USERS' => implode(' ', $dbUsers),
            'LINUX_USERS' => implode(' ', $linuxUsers),
            'PROVISION_DB_ADMIN_USER' => (string) config('provision.db_admin_user'),
            'PROVISION_DB_ADMIN_PASSWORD' => (string) config('provision.db_admin_password'),
        ];
        if ($metaFile !== '') {
            $values['META_FILE'] = $metaFile;
        }

        $this->writeCreds($path, $values);
    }

    /**
     * @param  array<string, string>  $values
     */
    private function writeCreds(string $path, array $values): void
    {
        $lines = [];
        foreach ($values as $key => $value) {
            $lines[] = $key.'='.$this->shellQuote($value);
        }

        file_put_contents($path, implode("\n", $lines)."\n");
        @chmod($path, 0600);
    }

    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    private function filterNames(array $names): array
    {
        $filtered = [];
        foreach ($names as $name) {
            if ($this->isDeletableName($name)) {
                $filtered[] = $name;
            }
        }

        return array_values(array_unique($filtered));
    }

    private function isDeletableName(string $name): bool
    {
        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,63}$/', $name)) {
            return false;
        }

        $protected = [
            'homenew',
            'root',
            'ubuntu',
            'www-data',
            'mysql',
            'provisioner',
            'provision',
            'admin',
            'nginx',
            'ftp',
        ];

        return ! in_array(strtolower($name), $protected, true);
    }

    public function isKeptSite(string $name): bool
    {
        return in_array(strtolower($name), ['homepagekorea', 'hkmr', 'hk-laravel', 'aseic'], true);
    }

    private function basePath(): string
    {
        return rtrim((string) config('provision.base_path', '/var/www/html'), '/');
    }
}
