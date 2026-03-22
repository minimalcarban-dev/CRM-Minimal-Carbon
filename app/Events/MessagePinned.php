<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagePinned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $channelId,
        public int $messageId,
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
            'action' => $this->action,
        ];
    }
}
