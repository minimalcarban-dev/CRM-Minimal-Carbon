<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'meta_account_id',
        'conversation_id',
        'platform',
        'last_message_at',
        'is_read',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAccount::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MetaMessage::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(MetaMessage::class)->latest()->limit(1);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('last_message_at', 'desc');
    }

    // ─────────────────────────────────────────────────────────────
    // Methods
    // ─────────────────────────────────────────────────────────────

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    public function updateLastMessageTime(): void
    {
        $this->update(['last_message_at' => now()]);
    }
}
