<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $channelId,
        public int $messageId,
        public int $adminId,
        public string $emoji,
        public string $action
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("chat.channel.{$this->channelId}")];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'admin_id' => $this->adminId,
            'emoji' => $this->emoji,
            'action' => $this->action,
        ];
    }
}
