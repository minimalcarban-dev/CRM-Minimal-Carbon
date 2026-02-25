<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeoIpService
{
    /**
     * Look up geolocation data for an IP address.
     * Uses ip-api.com free tier (45 req/min, no key needed).
     * Results are cached for 24 hours.
     */
    public static function lookup(string $ip): array
    {
        // Skip for local/private IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || self::isPrivateIp($ip)) {
            return [
                'country' => 'Local',
                'city' => 'Localhost',
                'region' => '',
                'isp' => 'Local Network',
                'latitude' => 0,
                'longitude' => 0,
            ];
        }

        $cacheKey = 'geoip:' . md5($ip);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($ip) {
            try {
                $response = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,country,regionName,city,lat,lon,isp',
                    ]);

                if ($response->successful() && $response->json('status') === 'success') {
                    $data = $response->json();
                    return [
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'isp' => $data['isp'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('GeoIP lookup failed for ' . $ip . ': ' . $e->getMessage());
            }

            return [
                'country' => null,
                'city' => null,
                'region' => null,
                'isp' => null,
                'latitude' => null,
                'longitude' => null,
            ];
        });
    }

    private static function isPrivateIp(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
