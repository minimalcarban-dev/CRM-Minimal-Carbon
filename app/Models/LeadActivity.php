<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'admin_id',
        'type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Activity Types
    const TYPE_MESSAGE_RECEIVED = 'message_received';
    const TYPE_MESSAGE_SENT = 'message_sent';
    const TYPE_STATUS_CHANGED = 'status_changed';
    const TYPE_ASSIGNED = 'assigned';
    const TYPE_UNASSIGNED = 'unassigned';
    const TYPE_NOTE_ADDED = 'note_added';
    const TYPE_SCORE_UPDATED = 'score_updated';
    const TYPE_PRIORITY_CHANGED = 'priority_changed';
    const TYPE_LEAD_CREATED = 'lead_created';
    const TYPE_LEAD_MERGED = 'lead_merged';

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_MESSAGE_RECEIVED => 'bi-chat-left-text',
            self::TYPE_MESSAGE_SENT => 'bi-chat-right-text',
            self::TYPE_STATUS_CHANGED => 'bi-arrow-repeat',
            self::TYPE_ASSIGNED => 'bi-person-check',
            self::TYPE_UNASSIGNED => 'bi-person-dash',
            self::TYPE_NOTE_ADDED => 'bi-sticky',
            self::TYPE_SCORE_UPDATED => 'bi-star',
            self::TYPE_PRIORITY_CHANGED => 'bi-flag',
            self::TYPE_LEAD_CREATED => 'bi-plus-circle',
            self::TYPE_LEAD_MERGED => 'bi-arrows-collapse',
            default => 'bi-dot'
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_MESSAGE_RECEIVED => 'primary',
            self::TYPE_MESSAGE_SENT => 'info',
            self::TYPE_STATUS_CHANGED => 'warning',
            self::TYPE_ASSIGNED, self::TYPE_UNASSIGNED => 'secondary',
            self::TYPE_NOTE_ADDED => 'dark',
            self::TYPE_SCORE_UPDATED => 'warning',
            self::TYPE_PRIORITY_CHANGED => 'danger',
            self::TYPE_LEAD_CREATED => 'success',
            self::TYPE_LEAD_MERGED => 'info',
            default => 'secondary'
        };
    }
}
