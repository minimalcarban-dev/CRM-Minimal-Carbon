<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserMentioned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $mentionedAdminId;
    public $afterCommit = true;

    public function __construct(int $adminId, Message $message)
    {
        $this->mentionedAdminId = $adminId;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.notifications.' . $this->mentionedAdminId)];
    }
}
