<?php

namespace App\Http\Middleware;

use App\Models\AdminAccessLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 인증된 관리자의 백오피스 요청을 admin_access_logs에 기록한다.
 */
class LogAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if ($user === null || ! $user->isAdmin()) {
            return $response;
        }

        // 세션 연장 API는 호출이 잦아 로그만 과다해지므로 제외
        if ($request->is('backoffice/session/extend')) {
            return $response;
        }

        try {
            AdminAccessLog::create([
                'admin_id' => $user->id,
                'name' => $user->name,
                'ip_address' => $request->ip() ?? '',
                'user_agent' => $request->userAgent(),
                'referer' => $this->truncate($request->header('referer'), 500),
                'access_path' => '/'.$request->path(),
                'http_method' => strtoupper($request->method()),
                'accessed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('관리자 접속 로그 기록 실패: '.$e->getMessage());
        }

        return $response;
    }

    private function truncate(?string $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }
}
