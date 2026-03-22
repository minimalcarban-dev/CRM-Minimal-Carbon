<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinnedMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'channel_id',
        'pinned_by',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function pinnedBy()
    {
        return $this->belongsTo(Admin::class, 'pinned_by');
    }
}
