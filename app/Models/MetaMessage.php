<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_conversation_id',
        'message_id',
        'direction',
        'content',
        'attachments',
        'status',
        'read_at',
        'sender_id',
        'sender_name',
    ];

    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';

    // Direction constants
    const DIRECTION_INCOMING = 'incoming';
    const DIRECTION_OUTGOING = 'outgoing';

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(MetaConversation::class, 'meta_conversation_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MetaMessageLog::class)->latest();
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeIncoming($query)
    {
        return $query->where('direction', self::DIRECTION_INCOMING);
    }

    public function scopeOutgoing($query)
    {
        return $query->where('direction', self::DIRECTION_OUTGOING);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ─────────────────────────────────────────────────────────────
    // Methods
    // ─────────────────────────────────────────────────────────────

    public function markAsDelivered(): void
    {
        $this->update(['status' => self::STATUS_DELIVERED]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => self::STATUS_READ,
            'read_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    public function isIncoming(): bool
    {
        return $this->direction === self::DIRECTION_INCOMING;
    }

    public function isOutgoing(): bool
    {
        return $this->direction === self::DIRECTION_OUTGOING;
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bi-clock',
            self::STATUS_SENT => 'bi-check',
            self::STATUS_DELIVERED => 'bi-check-all',
            self::STATUS_READ => 'bi-check-all text-primary',
            self::STATUS_FAILED => 'bi-x-circle text-danger',
            default => 'bi-dot'
        };
    }

    public function getFormattedAttachmentsAttribute(): array
    {
        if (!$this->attachments)
            return [];

        return collect($this->attachments)->map(function ($attachment) {
            $type = $attachment['type'] ?? 'file';
            return [
                'type' => $type,
                'url' => $attachment['url'] ?? '',
                'name' => $attachment['name'] ?? 'Attachment',
                'icon' => match ($type) {
                    'image' => 'bi-image',
                    'video' => 'bi-camera-video',
                    'audio' => 'bi-mic',
                    'file' => 'bi-file-earmark',
                    default => 'bi-paperclip'
                }
            ];
        })->toArray();
    }
}
