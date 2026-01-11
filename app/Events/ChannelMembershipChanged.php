<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChannelMembershipChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $channelId;
    public $action; // 'added' | 'removed'

    public $afterCommit = true;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, int $channelId, string $action)
    {
        $this->userId = $userId;
        $this->channelId = $channelId;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.notifications.' . $this->userId)];
    }
}
