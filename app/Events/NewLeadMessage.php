<?php

namespace App\Events;

use App\Models\Lead;
use App\Models\MetaMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewLeadMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $afterCommit = true;

    public Lead $lead;
    public MetaMessage $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Lead $lead, MetaMessage $message)
    {
        $this->lead = $lead;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to the assigned agent's private channel
        if ($this->lead->assigned_to) {
            $channels[] = new PrivateChannel('admin.leads.' . $this->lead->assigned_to);
        }

        // Also broadcast to a general leads channel for super admins
        $channels[] = new PrivateChannel('leads.inbox');

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'lead.message.new';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'lead_platform' => $this->lead->platform,
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'direction' => $this->message->direction,
                'created_at' => $this->message->created_at->toISOString(),
                'sender_name' => $this->message->sender_name,
            ],
        ];
    }
}
