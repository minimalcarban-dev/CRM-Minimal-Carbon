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
        $viteUrl = config('app.env') === 'local' ? 'http://localhost:5173' : '';

        // Allow self assets + jQuery CDN + inline styles (temporary) and images + ws connections + Vite dev server
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.jsdelivr.net";
        $styleSrc = "'self' 'unsafe-inline' https://cdn.jsdelivr.net";
        $connectSrc = "'self' https://* wss:";

        if ($viteUrl) {
            $scriptSrc .= " {$viteUrl}";
            $styleSrc .= " {$viteUrl}";
            $connectSrc .= " {$viteUrl} ws://localhost:5173";
        }

        $csp = "default-src 'self'; script-src {$scriptSrc}; style-src {$styleSrc}; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net data:; connect-src {$connectSrc}; frame-src 'self' https://res.cloudinary.com https://docs.google.com; frame-ancestors 'self'; object-src 'none'; base-uri 'self';";

        $response->headers->set('Content-Security-Policy', $csp);
        return $response;
    }
}
