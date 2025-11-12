<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Message extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'channel_id',
        'sender_id',
        'type',
        'body',
        'metadata',
        'reply_to_id',
        'edited_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'edited_at' => 'datetime',
    ];

    /**
     * Get the channel this message belongs to
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the sender of this message
     */
    public function sender()
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }

    /**
     * Get the message this message is replying to
     */
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Get messages replying to this message
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    /**
     * Get the attachments for this message
     */
    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Get the read receipts for this message
     */
    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Mark this message as read by the given user
     */
    public function markAsRead(Admin $user)
    {
        return $this->reads()->firstOrCreate(
            ['user_id' => $user->id],
            ['read_at' => now()]
        );
    }

    /**
     * Check if this message has been read by the given user
     */
    public function isReadBy(Admin $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'body' => $this->body,
            'channel_id' => $this->channel_id,
            'sender_id' => $this->sender_id,
            'type' => $this->type,
            'created_at' => $this->created_at->timestamp,
            'metadata' => is_array($this->metadata) ? json_encode($this->metadata) : $this->metadata,
        ];
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->id;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return 'id';
    }
}