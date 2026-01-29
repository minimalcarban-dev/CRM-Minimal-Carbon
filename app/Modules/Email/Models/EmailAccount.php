<?php

namespace App\Modules\Email\Models;

use App\Models\Admin;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class EmailAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email_address',
        'provider',
        'access_token',
        'refresh_token',
        'expires_in',
        'token_expires_at',
        'sync_token',
        'history_id',
        'sync_status',
        'sync_error',
        'last_sync_at',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * Get the users (admins) associated with this account.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            Admin::class,
            'email_account_users',
            'email_account_id',
            'user_id'
        )->withPivot('role', 'company_id')
         ->withTimestamps();
    }

    /**
     * Get the emails for this account.
     */
    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Get the audit logs for this account.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(EmailAuditLog::class);
    }

    /**
     * Check if the token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }
        return $this->token_expires_at->isPast();
    }

    /**
     * Accessor for access_token to decrypt it.
     */
    public function getAccessTokenAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Mutator for access_token to encrypt it.
     */
    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Accessor for refresh_token to decrypt it.
     */
    public function getRefreshTokenAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Mutator for refresh_token to encrypt it.
     */
    public function setRefreshTokenAttribute($value)
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }
}
