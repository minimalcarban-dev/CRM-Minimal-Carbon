<?php

namespace App\Modules\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email_account_id',
        'message_id',
        'thread_id',
        'subject',
        'from_name',
        'from_email',
        'to_recipients',
        'cc_recipients',
        'bcc_recipients',
        'body_html',
        'body_plain',
        'received_at',
        'has_attachments',
        'size_bytes',
        'labels',
        'headers',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'has_attachments' => 'boolean',
        'labels' => 'json',
        'headers' => 'json',
    ];

    /**
     * Get the account this email belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    /**
     * Get the attachments for this email.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    /**
     * Get the per-user states for this email.
     */
    public function userStates(): HasMany
    {
        return $this->hasMany(EmailUserState::class);
    }

    /**
     * Get all emails in the same thread (for conversation view).
     */
    public function threadMessages(): HasMany
    {
        return $this->hasMany(Email::class, 'thread_id', 'thread_id');
    }

    /**
     * Get the state for a specific user.
     */
    public function stateForUser(int $userId)
    {
        return $this->userStates()->where('user_id', $userId)->first();
    }
}
