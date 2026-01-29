<?php

namespace App\Modules\Email\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailUserState extends Model
{
    protected $fillable = [
        'email_id',
        'user_id',
        'is_read',
        'is_starred',
        'read_at',
        'starred_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'read_at' => 'datetime',
        'starred_at' => 'datetime',
    ];

    /**
     * Get the email this state belongs to.
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * Get the user (admin) this state belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
}
