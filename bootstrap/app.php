<?php

use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureAdminHasPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => EnsureAdminAuthenticated::class,
            'admin.permission' => EnsureAdminHasPermission::class,
            'chat.rate' => \App\Http\Middleware\ChatRateLimiter::class,
            'csp' => \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);
        // Global security headers
        $middleware->append(\App\Http\Middleware\ContentSecurityPolicy::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // For all admin pages and APIs: convert 403 to a friendly redirect
        // HTML requests -> redirect to dashboard
        // JSON/XHR -> return 403 with a redirect hint header the frontend can act on

        $redirectTo = '/admin/dashboard';

        // Policy/authorization exceptions
        $exceptions->render(function (AuthorizationException $e, Request $request) use ($redirectTo) {
            if ($request->is('admin/*')) {
                if ($request->expectsJson()) {
                    return response()
                        ->json(['message' => 'Forbidden', 'redirect' => $redirectTo], 403)
                        ->header('X-Redirect', $redirectTo);
                }
                return redirect()->to($redirectTo);
            }
        });

        // Any HttpException with status 403 coming from admin routes
        $exceptions->render(function (HttpException $e, Request $request) use ($redirectTo) {
            if ((int) $e->getStatusCode() === 403 && $request->is('admin/*')) {
                if ($request->expectsJson()) {
                    return response()
                        ->json(['message' => 'Forbidden', 'redirect' => $redirectTo], 403)
                        ->header('X-Redirect', $redirectTo);
                }
                return redirect()->to($redirectTo);
            }
        });
    })->create();
