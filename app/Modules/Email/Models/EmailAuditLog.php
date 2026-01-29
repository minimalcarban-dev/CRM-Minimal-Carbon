<?php

namespace App\Modules\Email\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAuditLog extends Model
{
    protected $fillable = [
        'email_account_id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Get the account this log belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
