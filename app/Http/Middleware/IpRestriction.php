<?php

namespace App\Http\Middleware;

use App\Models\AllowedIp;
use App\Models\AppSetting;
use App\Models\IpAccessLog;
use App\Services\GeoIpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpRestriction
{
    /**
     * Routes that are always accessible regardless of IP restriction.
     */
    protected array $excludedPaths = [
        'admin/login',
        'admin/login/*',
        'admin/settings/security',
        'admin/settings/security/*',
        'check-ip',
        'ip/request-access',
        'api/*',
        'webhook/*',
        'up', // health check
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If IP restriction is not enabled, allow all traffic
        if (!AppSetting::isEnabled('ip_restriction_enabled')) {
            return $next($request);
        }

        // Check if the current route is excluded
        foreach ($this->excludedPaths as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // Check if the visitor's IP is in the whitelist
        $clientIp = $request->ip();

        if (AllowedIp::isAllowed($clientIp)) {
            return $next($request);
        }

        // Log the blocked attempt (async-safe, fire & forget)
        $this->logBlockedAttempt($request, $clientIp);

        // IP is not allowed — return 403
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Access denied. Your IP address is not authorized.',
                'ip' => $clientIp,
            ], 403);
        }

        return response()->view('errors.403-ip', [
            'ip' => $clientIp,
        ], 403);
    }

    /**
     * Log the blocked IP attempt with geolocation.
     */
    protected function logBlockedAttempt(Request $request, string $clientIp): void
    {
        try {
            // Rate-limit logging: max 1 log per IP per 5 minutes
            $cacheKey = 'ip_log_throttle:' . md5($clientIp);
            if (cache()->has($cacheKey)) {
                return;
            }
            cache()->put($cacheKey, true, now()->addMinutes(5));

            $geo = GeoIpService::lookup($clientIp);

            IpAccessLog::create([
                'ip_address' => $clientIp,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'country' => $geo['country'],
                'city' => $geo['city'],
                'region' => $geo['region'],
                'isp' => $geo['isp'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'blocked_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail — don't break the 403 response
            \Illuminate\Support\Facades\Log::warning('IP access log failed: ' . $e->getMessage());
        }
    }
}
