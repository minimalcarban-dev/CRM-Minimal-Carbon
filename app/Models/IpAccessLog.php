<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpAccessLog extends Model
{
    protected $fillable = [
        'ip_address',
        'url',
        'method',
        'user_agent',
        'country',
        'city',
        'region',
        'isp',
        'latitude',
        'longitude',
        'blocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get location as a formatted string.
     */
    public function getLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        return implode(', ', $parts) ?: 'Unknown';
    }
}
