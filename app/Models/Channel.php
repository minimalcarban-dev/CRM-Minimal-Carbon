<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'settings',
        'last_message_at',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the users in this channel
     */
    public function users()
    {
        return $this->belongsToMany(Admin::class, 'channel_user')
            ->withPivot(['role', 'settings', 'last_read_at'])
            ->withTimestamps();
    }

    /**
     * Get all messages in the channel
     */
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message in the channel
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Check if the given user is a member of this channel
     */
    public function hasMember(Admin $user): bool
    {
        return $this->users()->where('admin_id', $user->id)->exists();
    }

    /**
     * Get unread messages count for a user
     */
    public function unreadCount(Admin $user): int
    {
        $lastRead = $this->users()
            ->where('admin_id', $user->id)
            ->value('last_read_at');

        if (!$lastRead) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $lastRead)
            ->where('sender_id', '!=', $user->id)
            ->count();
    }
}