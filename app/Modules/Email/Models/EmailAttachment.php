<?php

namespace App\Modules\Email\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAttachment extends Model
{
    protected $fillable = [
        'email_id',
        'attachment_id',
        'filename',
        'content_type',
        'size_bytes',
        'storage_path',
        'is_inline',
        'content_id',
    ];

    protected $casts = [
        'is_inline' => 'boolean',
    ];

    /**
     * Get the email this attachment belongs to.
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
