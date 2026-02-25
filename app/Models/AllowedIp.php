<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'label',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
     * Check if a given IP is whitelisted and active.
     */
    public static function isAllowed(string $ip): bool
    {
        return static::where('ip_address', $ip)->where('is_active', true)->exists();
    }
}
