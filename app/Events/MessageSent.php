<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $afterCommit = true;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        // Ensure relationships are loaded for broadcast
        $this->message = $message->load(['sender', 'attachments', 'reads']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.channel.' . $this->message->channel_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'channel_id' => $this->message->channel_id,
                'sender_id' => $this->message->sender_id,
                'body' => $this->message->body,
                'type' => $this->message->type,
                'metadata' => $this->message->metadata,
                'reply_to_id' => $this->message->reply_to_id,
                'thread_count' => $this->message->thread_count,
                'created_at' => $this->message->created_at?->toISOString(),
                'updated_at' => $this->message->updated_at?->toISOString(),
                'sender' => $this->message->sender ? [
                    'id' => $this->message->sender->id,
                    'name' => $this->message->sender->name,
                    'email' => $this->message->sender->email,
                ] : null,
                'attachments' => $this->message->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'filename' => $a->filename,
                    'path' => $a->path,
                    'thumbnail_path' => $a->thumbnail_path ?? null,
                    'mime_type' => $a->mime_type,
                    'size' => $a->size,
                ])->toArray(),
                'reads' => $this->message->reads->map(fn($r) => [
                    'user_id' => $r->user_id,
                    'read_at' => $r->read_at,
                ])->toArray(),
            ],
        ];
    }
}