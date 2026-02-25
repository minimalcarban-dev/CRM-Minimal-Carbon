<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpAccessRequest extends Model
{
    protected $fillable = [
        'ip_address',
        'name',
        'reason',
        'country',
        'city',
        'region',
        'isp',
        'latitude',
        'longitude',
        'user_agent',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function getLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        return implode(', ', $parts) ?: 'Unknown';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
