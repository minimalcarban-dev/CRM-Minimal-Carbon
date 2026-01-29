<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Build CSP based on environment
        $isLocal = config('app.env') === 'local';
        $viteUrl = $isLocal ? 'http://localhost:5173' : '';

        // Allow self assets + jQuery CDN + inline styles (temporary) and images + ws connections + Vite dev server
        // NOTE: 'unsafe-eval' should never be enabled in production (it weakens CSP and may trigger security scanners).
        $scriptSrc = "'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net";
        if ($isLocal) {
            $scriptSrc .= " 'unsafe-eval'";
        }
        $styleSrc = "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com";
        $connectSrc = "'self' https://* wss:";

        if ($viteUrl) {
            $scriptSrc .= " {$viteUrl}";
            $styleSrc .= " {$viteUrl}";
            $connectSrc .= " {$viteUrl} ws://localhost:5173";
        }

        $csp = "default-src 'self'; script-src {$scriptSrc}; style-src {$styleSrc}; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com data:; connect-src {$connectSrc}; frame-src 'self' https://res.cloudinary.com https://docs.google.com; frame-ancestors 'self'; object-src 'none'; base-uri 'self';";

        $response->headers->set('Content-Security-Policy', $csp);
        return $response;
    }
}
