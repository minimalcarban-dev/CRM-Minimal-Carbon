<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiamondAssignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $diamond;
    public $adminId;
    public $assignedBy;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($diamond, $adminId, $assignedBy, $message)
    {
        if (!$diamond) {
            throw new \InvalidArgumentException('Diamond cannot be null when assigning.');
        }

        $this->diamond = $diamond;
        $this->adminId = $adminId;
        $this->assignedBy = $assignedBy;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.' . $this->adminId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'title' => 'Diamond Assigned',
            'diamond_id' => $this->diamond->id,
            'diamond_sku' => $this->diamond->sku,
            'assigned_by' => $this->assignedBy,
            'message' => $this->message,
            'type' => 'info',
        ];
    }
}
