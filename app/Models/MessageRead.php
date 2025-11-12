<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the message that was read
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the user who read the message
     */
    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
}