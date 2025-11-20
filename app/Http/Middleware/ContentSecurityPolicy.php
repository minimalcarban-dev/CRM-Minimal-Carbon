<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Allow self assets + jQuery CDN + inline styles (temporary) and images + ws connections.
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net data:; connect-src 'self' https://* wss:; frame-ancestors 'self'; object-src 'none'; base-uri 'self';";

        return $response->header('Content-Security-Policy', $csp);

    }
}
