<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToNonWww
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (str_starts_with($host, 'www.')) {
            $targetHost = substr($host, 4);
            $targetUrl = $request->getScheme().'://'.$targetHost.$request->getRequestUri();

            return redirect()->to($targetUrl, 301);
        }

        return $next($request);
    }
}
