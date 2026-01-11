<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaMessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_message_id',
        'event_type',
        'api_response',
        'retry_count',
        'error_message',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function message(): BelongsTo
    {
        return $this->belongsTo(MetaMessage::class, 'meta_message_id');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeErrors($query)
    {
        return $query->whereNotNull('error_message');
    }

    public function scopeByEventType($query, string $type)
    {
        return $query->where('event_type', $type);
    }
}
