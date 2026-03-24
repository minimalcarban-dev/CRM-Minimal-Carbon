<?php

namespace App\Http\Middleware;

use App\Models\AllowedIp;
use App\Models\AppSetting;
use App\Models\IpAccessLog;
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
        'admin/settings/security',
        'admin/settings/security/*',
        'check-ip',
        'ip/request-access',
        'privacy-policy',
        'terms-of-service',
        'api/*',
        'webhook/*',
        'up', // health check
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
     * Handle an incoming request using the 3-Tier Hybrid Security Check:
     *
     * 1. Token Match  – cookie matches an active device_token in allowed_ips
     * 2. Browser Match – User-Agent exactly matches the stored user_agent (stops cookie theft)
     * 3. Geo-Fencing   – City+Country match (stops remote abuse)
     *
     * If all 3 pass, silently update last_used_at & the dynamic IP.
     * If any fail, fall back to legacy IP check or block.
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

        $clientIp = $request->ip();

        // ── Tier 1: Device Token Check ──────────────────────────────
        $deviceToken = $request->cookie($this->cookieName);

        if ($deviceToken) {
            $device = AllowedIp::findByDeviceToken($deviceToken);

            if ($device && !$device->isDeviceExpired($this->expiryDays)) {
                // ── Tier 2: Browser Fingerprint Check ───────────────
                $currentUA = $request->userAgent() ?? '';
                $storedUA  = $device->user_agent ?? '';

                if ($storedUA && $currentUA === $storedUA) {
                    // ── Tier 3: Geo-Fencing Check ───────────────────
                    $geo = GeoIpService::lookup($clientIp);
                    $currentCity    = $geo['city'] ?? null;
                    $currentCountry = $geo['country'] ?? null;

                    $geoPass = $this->geoFenceCheck(
                        $device->city,
                        $device->country,
                        $currentCity,
                        $currentCountry
                    );

                    if ($geoPass) {
                        // ✅ All 3 tiers passed — silently update
                        $device->update([
                            'ip_address'  => $clientIp,
                            'last_used_at' => now(),
                            'city'        => $currentCity,
                            'country'     => $currentCountry,
                        ]);

                        return $next($request);
                    }
                }

                // If Tier 2 or 3 failed, log the anomaly
                Log::warning("Device trust anomaly for token {$deviceToken}: UA or Geo mismatch from {$clientIp}");
                
                // Audit Log entry for the anomaly
                \App\Services\AuditLogger::log('Device Trust Anomaly', $device, null, [
                    'ip' => $clientIp,
                    'user_agent' => substr($currentUA, 0, 500),
                    'city' => $currentCity,
                    'country' => $currentCountry
                ], ['status' => 'blocked']);
            }
        }

        // ── Pending Device Token Upgrade ────────────────────────────
        // When admin approves a request, a matching AllowedIp record is
        // created with the same token. When the blocked user revisits,
        // we find their pending token, validate the 3-tier check, and
        // upgrade to a permanent device_trust_token cookie.
        $pendingToken = $request->cookie('pending_device_token');

        if ($pendingToken) {
            $device = AllowedIp::findByDeviceToken($pendingToken);

            if ($device && !$device->isDeviceExpired($this->expiryDays)) {
                $currentUA = $request->userAgent() ?? '';

                // For pending tokens, learn the UA on first use if not set
                if (!$device->user_agent) {
                    $device->update(['user_agent' => substr($currentUA, 0, 500)]);
                }

                $geo = GeoIpService::lookup($clientIp);

                $device->update([
                    'ip_address'   => $clientIp,
                    'last_used_at' => now(),
                    'city'         => $geo['city'] ?? null,
                    'country'      => $geo['country'] ?? null,
                ]);

                // Set the permanent device trust cookie and clear the pending one
                $trustCookie = cookie(
                    $this->cookieName,
                    $pendingToken,
                    60 * 24 * $this->expiryDays,
                    '/',
                    null,
                    true,   // secure
                    true,   // httpOnly
                    false,
                    'Lax'
                );

                $forgetPending = cookie()->forget('pending_device_token');

                return $next($request)
                    ->withCookie($trustCookie)
                    ->withCookie($forgetPending);
            }
        }

        // ── Fallback: Legacy IP whitelist check ─────────────────────
        if (AllowedIp::isAllowed($clientIp)) {
            return $next($request);
        }

        // Log the blocked attempt
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
     * Compare the stored geo location against the current visitor's geo.
     * Returns true when they match, or when either side has no data (graceful).
     */
    protected function geoFenceCheck(
        ?string $storedCity,
        ?string $storedCountry,
        ?string $currentCity,
        ?string $currentCountry,
    ): bool {
        // If we have no stored geo data yet, pass (first-time tolerance)
        if (!$storedCity && !$storedCountry) {
            return true;
        }

        // If current geo lookup returned nothing (API down), pass gracefully
        if (!$currentCity && !$currentCountry) {
            return true;
        }

        // Country MUST match
        if ($storedCountry && $currentCountry && $storedCountry !== $currentCountry) {
            return false;
        }

        // City must match when both are available
        if ($storedCity && $currentCity && $storedCity !== $currentCity) {
            return false;
        }

        return true;
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
            Log::warning('IP access log failed: ' . $e->getMessage());
        }
    }
}
