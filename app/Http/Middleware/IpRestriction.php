<?php

namespace App\Http\Middleware;

use App\Models\AllowedIp;
use App\Models\AppSetting;
use App\Models\IpAccessLog;
use App\Services\AuditLogger;
use App\Services\GeoIpService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IpRestriction
{
    /**
     * Routes that are always accessible regardless of IP restriction.
     */
    protected array $excludedPaths = [
        'admin/login',
        'admin/login/*',
        'admin/logout',
        'admin/settings/security',
        'admin/settings/security/*',
        'check-ip',
        'ip/request-access',
        'privacy-policy',
        'terms-of-service',
        'api/*',
        'webhook/*',
        'up',
    ];

    /**
     * Cookie name for the device trust token.
     */
    protected string $cookieName = 'device_trust_token';

    /**
     * Number of days before an unused device token expires.
     */
    protected int $expiryDays = 30;

    /**
     * Handle an incoming request using a trusted-device model.
     *
     * A valid device trust token is the primary access signal.
     * IP and GeoIP are audit signals only and must never block a trusted browser.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!AppSetting::isEnabled('ip_restriction_enabled')) {
            return $next($request);
        }

        foreach ($this->excludedPaths as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        $clientIp = $request->ip();
        $currentUA = $request->userAgent() ?? '';

        $deviceToken = $request->cookie($this->cookieName);
        if ($deviceToken) {
            $device = AllowedIp::findByDeviceToken($deviceToken);

            if ($device && !$device->isDeviceExpired($this->expiryDays)) {
                $geo = GeoIpService::lookup($clientIp);
                $oldValues = [
                    'ip_address' => $device->ip_address,
                    'user_agent' => $device->user_agent,
                    'city' => $device->city,
                    'country' => $device->country,
                    'last_used_at' => optional($device->last_used_at)?->toDateTimeString(),
                ];
                $newValues = [
                    'ip_address' => $clientIp,
                    'user_agent' => $device->user_agent ?: substr($currentUA, 0, 500),
                    'city' => $geo['city'] ?? null,
                    'country' => $geo['country'] ?? null,
                    'last_used_at' => now()->toDateTimeString(),
                    'ip_changed' => $device->ip_address !== $clientIp,
                    'browser_changed' => ($device->user_agent ?? '') !== '' && $device->user_agent !== $currentUA,
                    'geo_changed' => $this->geoChanged(
                        $device->city,
                        $device->country,
                        $geo['city'] ?? null,
                        $geo['country'] ?? null
                    ),
                ];

                $device->update([
                    'ip_address' => $clientIp,
                    'last_used_at' => now(),
                    'user_agent' => $device->user_agent ?: substr($currentUA, 0, 500),
                    'city' => $geo['city'] ?? null,
                    'country' => $geo['country'] ?? null,
                ]);

                AuditLogger::log('Trusted Device Accessed', $device, null, $oldValues, $newValues);

                return $next($request);
            }

            Log::warning("Trusted device token did not match an active record or expired from {$clientIp}");
        }

        $pendingToken = $request->cookie('pending_device_token');
        if ($pendingToken) {
            $device = AllowedIp::findByDeviceToken($pendingToken);

            if ($device && !$device->isDeviceExpired($this->expiryDays)) {
                $geo = GeoIpService::lookup($clientIp);
                $oldValues = [
                    'ip_address' => $device->ip_address,
                    'user_agent' => $device->user_agent,
                    'city' => $device->city,
                    'country' => $device->country,
                    'last_used_at' => optional($device->last_used_at)?->toDateTimeString(),
                ];
                $newValues = [
                    'ip_address' => $clientIp,
                    'user_agent' => $device->user_agent ?: substr($currentUA, 0, 500),
                    'city' => $geo['city'] ?? null,
                    'country' => $geo['country'] ?? null,
                    'last_used_at' => now()->toDateTimeString(),
                    'device_token_upgraded' => true,
                ];

                $device->update([
                    'ip_address' => $clientIp,
                    'last_used_at' => now(),
                    'user_agent' => $device->user_agent ?: substr($currentUA, 0, 500),
                    'city' => $geo['city'] ?? null,
                    'country' => $geo['country'] ?? null,
                ]);

                $trustCookie = cookie(
                    $this->cookieName,
                    $pendingToken,
                    60 * 24 * $this->expiryDays,
                    '/',
                    null,
                    true,
                    true,
                    false,
                    'Lax'
                );

                $forgetPending = cookie()->forget('pending_device_token');

                AuditLogger::log('Trusted Device Upgraded', $device, null, $oldValues, $newValues);

                return $next($request)
                    ->withCookie($trustCookie)
                    ->withCookie($forgetPending);
            }
        }

        $this->logBlockedAttempt($request, $clientIp, $currentUA);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Access denied. Trusted device token is missing or expired.',
                'ip' => $clientIp,
            ], 403);
        }

        if (app()->runningUnitTests()) {
            return response('Access denied. Trusted device token is missing or expired.', 403);
        }

        return response()->view('errors.403-ip', [
            'ip' => $clientIp,
        ], 403);
    }

    /**
     * Compare geo attributes without making them access decisions.
     */
    protected function geoChanged(
        ?string $storedCity,
        ?string $storedCountry,
        ?string $currentCity,
        ?string $currentCountry,
    ): bool {
        return $storedCity !== $currentCity || $storedCountry !== $currentCountry;
    }

    /**
     * Log blocked attempts with geolocation.
     */
    protected function logBlockedAttempt(Request $request, string $clientIp, string $userAgent = ''): void
    {
        try {
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
                'user_agent' => substr($userAgent, 0, 500),
                'country' => $geo['country'],
                'city' => $geo['city'],
                'region' => $geo['region'],
                'isp' => $geo['isp'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'blocked_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('IP access log failed: ' . $e->getMessage());
        }
    }
}
