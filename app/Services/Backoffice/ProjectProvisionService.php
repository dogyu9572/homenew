<?php

namespace App\Services\Backoffice;

use App\Models\ProjectProvision;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
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
        ]));

        $missing = $this->missingConfig();
        if ($missing !== []) {
            $this->fail($provision, '서버 설정이 없습니다: '.implode(', ', $missing));

            return;
        }

        $script = (string) config('provision.script');
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
    private function missingConfig(): array
    {
        $required = [
            'PROVISION_DB_ADMIN_PASSWORD' => config('provision.db_admin_password'),
            'GITHUB_TOKEN' => config('provision.github_token'),
            'PROVISION_TEMPLATE_MIRROR' => config('provision.template_mirror'),
            'PROVISION_BASE_PATH' => config('provision.base_path'),
            'PROVISION_DOMAIN_SUFFIX' => config('provision.domain_suffix'),
        ];

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
            'ROOT_PASSWORD' => $provision->root_password,
            'FTP_USERNAME' => $provision->ftp_username,
            'FTP_PASSWORD' => $provision->ftp_password,
            'DB_USERNAME' => $provision->db_username,
            'DB_PASSWORD' => $provision->db_password,
            'DB_DATABASE' => $provision->db_database,
            'PROVISION_DB_ADMIN_USER' => (string) config('provision.db_admin_user'),
            'PROVISION_DB_ADMIN_PASSWORD' => (string) config('provision.db_admin_password'),
            'GITHUB_TOKEN' => (string) config('provision.github_token'),
            'GITHUB_ORG' => (string) config('provision.github_org'),
            'TEMPLATE_MIRROR' => (string) config('provision.template_mirror'),
            'BASE_PATH' => (string) config('provision.base_path'),
            'DOMAIN_SUFFIX' => (string) config('provision.domain_suffix'),
            'WEB_USER' => (string) config('provision.web_user'),
            'PROVISION_PHP_SOCKET' => (string) config('provision.php_socket'),
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

        if (str_starts_with($line, '[[RESULT_GIT]] ')) {
            $provision->git_url = trim(substr($line, strlen('[[RESULT_GIT]] ')));
            $provision->save();

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
}
