<?php

use App\Http\Middleware\BackOfficeAuth;
use App\Http\Middleware\LogAdminAccess;
use App\Http\Middleware\RedirectToNonWww;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request as HttpRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 프록시/로드밸런서 환경에서 실제 클라이언트 IP를 신뢰하도록 설정
        // 운영 환경에서는 TRUSTED_PROXIES를 명시 IP/CIDR로 제한하는 것을 권장
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: HttpRequest::HEADER_X_FORWARDED_AWS_ELB
                | HttpRequest::HEADER_X_FORWARDED_FOR
                | HttpRequest::HEADER_X_FORWARDED_HOST
                | HttpRequest::HEADER_X_FORWARDED_PORT
                | HttpRequest::HEADER_X_FORWARDED_PROTO
        );

        // 백오피스 경로에 대해 BackOfficeAuth 미들웨어 등록
        $middleware->group('backoffice', [
            BackOfficeAuth::class,
            LogAdminAccess::class,
        ]);

        // www 호스트 접근을 non-www로 통일
        $middleware->prepend(RedirectToNonWww::class);

        // 방문자 추적 미들웨어를 전역에 등록
        $middleware->append(TrackVisitor::class);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
