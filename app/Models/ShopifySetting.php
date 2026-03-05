<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifySetting extends Model
{
    protected $fillable = [
        'store_url',
        'access_token',
        'api_version',
        'is_active',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
    ];

    /**
     * Scope: only active settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the currently active Shopify setting.
     */
    public static function current(): ?self
    {
        return static::active()->latest()->first();
    }
}
