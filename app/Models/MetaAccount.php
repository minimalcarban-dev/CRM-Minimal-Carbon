<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class MetaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'account_id',
        'account_name',
        'page_id',
        'access_token',
        'token_expires_at',
        'is_active',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function conversations(): HasMany
    {
        return $this->hasMany(MetaConversation::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Token Management (Encrypted Storage)
    // ─────────────────────────────────────────────────────────────

    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    public function getDecryptedTokenAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['access_token']);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false; // If no expiry, assume valid
        }
        return $this->token_expires_at->isPast();
    }

    public function isTokenExpiringSoon(int $hours = 24): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }
        return $this->token_expires_at->diffInHours(now()) <= $hours;
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeFacebook($query)
    {
        return $query->where('platform', 'facebook');
    }

    public function scopeInstagram($query)
    {
        return $query->where('platform', 'instagram');
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'instagram' => 'bi-instagram',
            'facebook' => 'bi-facebook',
            default => 'bi-chat-dots'
        };
    }
}
