<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiamondReassignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $diamondId;
    public string $diamondSku;
    public $adminId;
    public $reassignedBy;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($diamond, $adminId, $reassignedBy, $message)
    {
        $this->diamond = $diamond;
        $this->adminId = $adminId;
        $this->reassignedBy = $reassignedBy;
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
            'title' => 'Diamond Reassigned',
            'diamond_id' => $this->diamond->id,
            'diamond_sku' => $this->diamond->sku,
            'assigned_by' => $this->reassignedBy,
            'message' => $this->message,
            'type' => 'warning'
        ];
    }
}
