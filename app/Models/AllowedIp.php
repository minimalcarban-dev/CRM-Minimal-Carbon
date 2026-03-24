<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'device_token',
        'user_agent',
        'last_used_at',
        'city',
        'country',
        'label',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * The admin who added this IP.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    /**
     * Scope to only active IPs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a given IP is whitelisted and active (legacy IP-only check).
     */
    public static function isAllowed(string $ip): bool
    {
        return static::where('ip_address', $ip)->where('is_active', true)->exists();
    }

    /**
     * Find an active device trust record by its unique token.
     */
    public static function findByDeviceToken(string $token): ?self
    {
        return static::where('device_token', $token)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check whether the device trust has expired due to inactivity.
     * Uses a 30-day sliding window.
     */
    public function isDeviceExpired(int $days = 30): bool
    {
        if (!$this->last_used_at) {
            // If never used, consider it expired only if created > $days ago
            return $this->created_at->diffInDays(now()) > $days;
        }

        return $this->last_used_at->diffInDays(now()) > $days;
    }

    /**
     * Extract a simplified browser name from a full User-Agent string.
     * Useful for UI display.
     */
    public static function parseBrowserName(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        if (str_contains($userAgent, 'Edg/'))   return 'Microsoft Edge';
        if (str_contains($userAgent, 'OPR/'))    return 'Opera';
        if (str_contains($userAgent, 'Chrome/')) return 'Google Chrome';
        if (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome')) return 'Safari';
        if (str_contains($userAgent, 'Firefox/')) return 'Mozilla Firefox';

        return 'Other';
    }
}
