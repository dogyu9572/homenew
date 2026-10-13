<?php

namespace App\Services\Backoffice;

use App\Jobs\DeployProjectSourceJob;
use App\Models\ProjectDeploy;
use App\Models\ProjectProvision;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ProjectDeployService
{
    public function createFromProvision(ProjectProvision $provision): void
    {
        $gitUrl = trim((string) $provision->git_url);
        if ($gitUrl === '') {
            return;
        }
        if (ProjectDeploy::query()->where('project_provision_id', $provision->id)->exists()) {
            return;
        }
        if (ProjectDeploy::query()->where('project_name', $provision->project_name)->exists()) {
            return;
        }

        $scheme = (string) config('provision.url_scheme', 'https');
        $suffix = (string) config('provision.domain_suffix', 'hk-test.co.kr');
        $base = rtrim((string) config('provision.base_path', '/var/www/html'), '/');
        $name = (string) $provision->project_name;
        $isJava = $provision->stack === ProjectProvision::STACK_JAVA;
        $row = [
            'project_provision_id' => $provision->id,
            'customer_name' => $provision->customer_name,
            'project_name' => $name,
            'site_url' => $provision->site_url ?: $scheme.'://'.$name.'.'.$suffix,
            'server_ip' => (string) config('provision.server_ip', ''),
            'ftp_username' => $provision->ftp_username,
            'ftp_password' => $provision->ftp_password,
            'source_path' => $base.'/'.$name,
            'git_url' => $gitUrl,
            'auto_deploy' => true,
            'environment' => ProjectDeploy::ENV_DEV,
            'stack' => $isJava ? ProjectDeploy::STACK_JAVA : ProjectDeploy::STACK_LARAVEL,
        ];
        if ($isJava) {
            $row['admin_site_url'] = $scheme.'://'.$name.'-adm.'.$suffix;
            $row['build_targets'] = [
                [
                    'type' => 'jar',
                    'module_path' => 'NOVA_ADM',
                    'artifact' => 'nova-adm-1.0.0.jar',
                    'deploy_path' => '/opt/'.$name.'/adm/nova-adm-1.0.0.jar',
                    'service_name' => $name.'-adm',
                ],
                [
                    'type' => 'jar',
                    'module_path' => 'NOVA_USER',
                    'artifact' => 'nova-user-1.0.0.jar',
                    'deploy_path' => '/opt/'.$name.'/user/nova-user-1.0.0.jar',
                    'service_name' => $name.'-user',
                ],
                [
                    'type' => 'next',
                    'module_path' => 'NOVA_USER/front-end',
                    'artifact' => '',
                    'deploy_path' => $base.'/'.$name.'/www',
                    'service_name' => 'nginx',
                ],
                [
                    'type' => 'next',
                    'module_path' => 'NOVA_ADM/front-end',
                    'artifact' => '',
                    'deploy_path' => $base.'/'.$name.'/www-adm',
                    'service_name' => 'nginx',
                ],
            ];
        }

        ProjectDeploy::query()->create($row);
    }

    public function queuePull(string $repositoryUrl, string $branch): void
    {
        if (! in_array($branch, ['main', 'master'], true)) {
            return;
        }

        $needle = $this->normalizeGitUrl($repositoryUrl);
        if ($needle === '') {
            return;
        }

        ProjectDeploy::query()
            ->where('auto_deploy', true)
            ->orderBy('id')
            ->get()
            ->filter(fn (ProjectDeploy $deploy) => $this->normalizeGitUrl($deploy->git_url) === $needle)
            ->each(fn (ProjectDeploy $deploy) => DeployProjectSourceJob::dispatch($deploy->id, $branch));
    }

    public function pull(ProjectDeploy $deploy, string $branch): void
    {
        if (! in_array($branch, ['main', 'master'], true)) {
            throw new RuntimeException('반영할 수 없는 브랜치입니다.');
        }

        if ($this->isLocalServer($deploy)) {
            $this->localPull($deploy, $branch);
        } else {
            $this->remotePull($deploy, $branch);
        }

        if ($deploy->stack === ProjectDeploy::STACK_JAVA) {
            $this->buildJava($deploy);
        }
    }

    private function isLocalServer(ProjectDeploy $deploy): bool
    {
        $serverIp = trim((string) config('provision.server_ip', ''));

        return $serverIp !== '' && $deploy->server_ip === $serverIp;
    }

    private function localPull(ProjectDeploy $deploy, string $branch): void
    {
        $path = $deploy->source_path;
        $this->assertLocalPath($path);
        $this->assertOrigin($path, $deploy->git_url);
        $helper = $this->writeCredentialHelper((string) config('provision.github_token'));

        try {
            $process = Process::timeout(180)->run([
                'sudo', '-n', '/usr/bin/git',
                '-c', 'safe.directory='.$path,
                '-C', $path,
                '-c', 'credential.helper=!'.$helper,
                'pull', '--ff-only', 'origin', $branch,
            ]);
            if (! $process->successful()) {
                throw new RuntimeException($this->safeMessage($process->errorOutput() ?: $process->output()));
            }

            $owner = $deploy->ftp_username.':'.(string) config('provision.web_user', 'www-data');
            $chown = Process::timeout(180)->run(['sudo', '-n', '/usr/bin/chown', '-R', $owner, $path]);
            if (! $chown->successful()) {
                throw new RuntimeException('소스 소유권 변경에 실패했습니다.');
            }
        } finally {
            if (is_file($helper)) {
                @unlink($helper);
            }
        }
    }

    private function remotePull(ProjectDeploy $deploy, string $branch): void
    {
        $path = $deploy->source_path;
        $this->assertSafePath($path);
        if (filter_var($deploy->server_ip, FILTER_VALIDATE_IP) === false) {
            throw new RuntimeException('배포 서버 IP가 올바르지 않습니다.');
        }
        if (! preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,31}$/', $deploy->ftp_username)) {
            throw new RuntimeException('배포 계정이 올바르지 않습니다.');
        }

        $token = (string) config('provision.github_token');
        if ($token === '') {
            throw new RuntimeException('깃 토큰이 없습니다.');
        }

        $expected = $this->normalizeGitUrl($deploy->git_url);
        if (preg_match('/["$`\\\\\r\n]/', $expected)) {
            throw new RuntimeException('깃 주소가 올바르지 않습니다.');
        }

        $askpass = $this->writeAskpass($deploy->ftp_password);
        $script = $this->remotePullScript(base64_encode($token), $expected);

        try {
            $process = Process::timeout(180)
                ->env([
                    'PATH' => getenv('PATH') ?: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                    'HOME' => getenv('HOME') ?: '/tmp',
                    'DISPLAY' => '1',
                    'SSH_ASKPASS' => $askpass,
                    'SSH_ASKPASS_REQUIRE' => 'force',
                ])
                ->input($script)
                ->run([
                    'ssh',
                    '-o', 'StrictHostKeyChecking=accept-new',
                    '-o', 'PreferredAuthentications=password',
                    '-o', 'PubkeyAuthentication=no',
                    '-o', 'NumberOfPasswordPrompts=1',
                    '-o', 'ConnectTimeout=20',
                    $deploy->ftp_username.'@'.$deploy->server_ip,
                    'bash', '-s', '--', $path, $branch,
                ]);
            if (! $process->successful()) {
                throw new RuntimeException($this->safeMessage($process->errorOutput() ?: $process->output()));
            }
        } finally {
            if (is_file($askpass)) {
                @unlink($askpass);
            }
        }
    }

    private function assertLocalPath(string $path): void
    {
        $this->assertSafePath($path);
        $base = rtrim((string) config('provision.base_path', '/var/www/html'), '/');
        if ($base === '' || $base === '/' || ! str_starts_with($path, $base.'/')) {
            throw new RuntimeException('소스 위치가 허용된 경로가 아닙니다.');
        }
        $real = realpath($path);
        if ($real === false || $real !== $path) {
            throw new RuntimeException('소스 위치가 없습니다.');
        }
    }

    private function assertSafePath(string $path): void
    {
        if (! preg_match('#^/(?!.*\.\.)[A-Za-z0-9/_.-]+$#', $path) || $path === '/') {
            throw new RuntimeException('소스 위치가 올바르지 않습니다.');
        }
    }

    private function assertOrigin(string $path, string $gitUrl): void
    {
        $process = Process::timeout(30)->run([
            'sudo', '-n', '/usr/bin/git',
            '-c', 'safe.directory='.$path,
            '-C', $path,
            'remote', 'get-url', 'origin',
        ]);
        if (! $process->successful()) {
            throw new RuntimeException('깃 저장소 주소를 확인하지 못했습니다.');
        }
        if ($this->normalizeGitUrl($process->output()) !== $this->normalizeGitUrl($gitUrl)) {
            throw new RuntimeException('깃 주소가 일치하지 않습니다.');
        }
    }

    private function normalizeGitUrl(string $url): string
    {
        $url = trim($url);
        $url = preg_replace('#^https://[^@]+@#i', 'https://', $url) ?? $url;
        $url = strtolower($url);
        $url = preg_replace('#\.git$#', '', $url) ?? $url;
        $url = preg_replace('#^git@github\.com:#', 'https://github.com/', $url) ?? $url;

        return rtrim($url, '/');
    }

    private function writeCredentialHelper(string $token): string
    {
        if ($token === '') {
            throw new RuntimeException('깃 토큰이 없습니다.');
        }

        $path = storage_path('app/deploy-credential-'.bin2hex(random_bytes(8)));
        $encoded = base64_encode($token);
        file_put_contents($path, "#!/bin/sh\nprintf 'username=x-access-token\\npassword=%s\\n' \"\$(printf '%s' '{$encoded}' | base64 -d)\"\n");
        chmod($path, 0700);

        return $path;
    }

    private function writeAskpass(string $password): string
    {
        $path = storage_path('app/deploy-askpass-'.bin2hex(random_bytes(8)));
        $encoded = base64_encode($password);
        file_put_contents($path, "#!/bin/sh\nprintf '%s\\n' \"\$(printf '%s' '{$encoded}' | base64 -d)\"\n");
        chmod($path, 0700);

        return $path;
    }

    private function remotePullScript(string $tokenBase64, string $expectedUrl): string
    {
        $script = <<<'BASH'
set -euo pipefail
path="$1"
branch="$2"
case "$path" in
    /*) ;;
    *) echo "소스 위치가 올바르지 않습니다." >&2; exit 1 ;;
esac
case "$path" in
    *..*) echo "소스 위치가 올바르지 않습니다." >&2; exit 1 ;;
esac
actual=$(git -c safe.directory="$path" -C "$path" remote get-url origin)
actual=${actual%.git}
actual=${actual%/}
actual=$(printf '%s' "$actual" | tr '[:upper:]' '[:lower:]')
actual=$(printf '%s' "$actual" | sed -E 's#https://[^@]+@#https://#')
if [ "$actual" != "__EXPECTED__" ]; then
    echo "깃 주소가 일치하지 않습니다." >&2
    exit 1
fi
helper=$(mktemp)
chmod 700 "$helper"
cat > "$helper" <<EOF
#!/bin/sh
printf '%s\n' 'username=x-access-token' "password=$(printf '%s' '__TOKEN_B64__' | base64 -d)"
EOF
chmod 700 "$helper"
git -c safe.directory="$path" -C "$path" -c "credential.helper=!$helper" pull --ff-only origin "$branch"
rm -f "$helper"
BASH;

        return str_replace(['__EXPECTED__', '__TOKEN_B64__'], [$expectedUrl, $tokenBase64], $script);
    }

    private function buildJava(ProjectDeploy $deploy): void
    {
        $targets = $deploy->build_targets;
        if (! is_array($targets) || $targets === []) {
            throw new RuntimeException('자바 빌드 대상이 없습니다.');
        }

        $profile = $deploy->environment === ProjectDeploy::ENV_LIVE ? 'prod' : '-';
        $lines = [$deploy->ftp_username."\t".$profile."\t".$deploy->source_path];

        foreach ($targets as $target) {
            if (! is_array($target)) {
                throw new RuntimeException('자바 빌드 대상이 올바르지 않습니다.');
            }

            $type = (string) ($target['type'] ?? '');
            $module = (string) ($target['module_path'] ?? '');
            $artifact = $type === 'jar' ? (string) ($target['artifact'] ?? '') : '-';
            $dest = (string) ($target['deploy_path'] ?? '');
            $service = (string) ($target['service_name'] ?? '');
            $this->assertBuildTarget($type, $module, $artifact, $dest, $service);
            $lines[] = implode("\t", [$type, $module, $artifact, $dest, $service]);
        }

        $spec = implode("\n", $lines)."\n";

        if ($this->isLocalServer($deploy)) {
            $this->localJavaBuild($spec);

            return;
        }

        $this->remoteJavaBuild($deploy, $spec);
    }

    private function assertBuildTarget(string $type, string $module, string $artifact, string $dest, string $service): void
    {
        if (! in_array($type, ['jar', 'next'], true)) {
            throw new RuntimeException('빌드 유형이 올바르지 않습니다.');
        }
        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_.\/-]{0,119}$/', $module) || str_contains($module, '..')) {
            throw new RuntimeException('모듈 경로가 올바르지 않습니다.');
        }
        if (! preg_match('#^/(opt|var/www)/(?!.*\.\.)[A-Za-z0-9/_.-]+$#', $dest)) {
            throw new RuntimeException('배포 경로가 올바르지 않습니다.');
        }
        if (! preg_match('/^[A-Za-z][A-Za-z0-9_.@-]{0,63}$/', $service)) {
            throw new RuntimeException('서비스명이 올바르지 않습니다.');
        }
        if ($type === 'jar' && (! preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.jar$/', $artifact) || ! str_ends_with($dest, '/'.$artifact))) {
            throw new RuntimeException('jar 배포 경로가 올바르지 않습니다.');
        }
    }

    private function localJavaBuild(string $spec): void
    {
        $script = '/usr/local/sbin/hk-java-build.sh';
        if (! is_file($script)) {
            throw new RuntimeException('자바 빌드 스크립트가 서버에 없습니다.');
        }

        $path = storage_path('app/java-build-'.bin2hex(random_bytes(8)));
        file_put_contents($path, $spec);
        chmod($path, 0600);

        try {
            $process = Process::timeout(1400)->run(['sudo', '-n', '/bin/bash', $script, $path]);
            if (! $process->successful()) {
                throw new RuntimeException($this->tailMessage($process->errorOutput() ?: $process->output()));
            }
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function remoteJavaBuild(ProjectDeploy $deploy, string $spec): void
    {
        $scriptPath = base_path('scripts/hk-java-build.sh');
        $script = is_file($scriptPath) ? file_get_contents($scriptPath) : false;
        if (! is_string($script) || $script === '') {
            throw new RuntimeException('자바 빌드 스크립트가 없습니다.');
        }

        $askpass = $this->writeAskpass($deploy->ftp_password);

        try {
            $process = Process::timeout(1400)
                ->env([
                    'PATH' => getenv('PATH') ?: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                    'HOME' => getenv('HOME') ?: '/tmp',
                    'DISPLAY' => '1',
                    'SSH_ASKPASS' => $askpass,
                    'SSH_ASKPASS_REQUIRE' => 'force',
                ])
                ->input($script)
                ->run([
                    'ssh',
                    '-o', 'StrictHostKeyChecking=accept-new',
                    '-o', 'PreferredAuthentications=password',
                    '-o', 'PubkeyAuthentication=no',
                    '-o', 'NumberOfPasswordPrompts=1',
                    '-o', 'ConnectTimeout=20',
                    $deploy->ftp_username.'@'.$deploy->server_ip,
                    'bash', '-s', '--', '--b64', base64_encode($spec),
                ]);
            if (! $process->successful()) {
                throw new RuntimeException($this->tailMessage($process->errorOutput() ?: $process->output()));
            }
        } finally {
            if (is_file($askpass)) {
                @unlink($askpass);
            }
        }
    }

    private function tailMessage(string $message): string
    {
        if (strlen($message) > 1500) {
            $message = substr($message, -1500);
        }

        return $this->safeMessage($message);
    }

    private function safeMessage(string $message): string
    {
        $message = trim($message);
        $message = preg_replace('#https://[^@\s]+@#', 'https://', $message) ?? '';

        return $message !== '' ? $message : '소스 반영에 실패했습니다.';
    }
}
