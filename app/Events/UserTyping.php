<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an admin is typing in a chat channel.
 * Uses ShouldBroadcastNow to avoid queue delay — typing must be real-time.
 */
class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $name;
    public int $channelId;
    public string $channelName;

    public function __construct(int $userId, string $name, int $channelId, string $channelName = '')
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->channelId = $channelId;
        $this->channelName = $channelName;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.channel.' . $this->channelId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'UserTyping';
    }

    /**
     * Data to broadcast with the event.
     */
    public function broadcastWith(): array
    {
        return [
            'userId' => $this->userId,
            'name' => $this->name,
            'channelId' => $this->channelId,
            'channelName' => $this->channelName,
        ];
    }
}
