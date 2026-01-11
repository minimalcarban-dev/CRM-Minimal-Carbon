<?php

namespace App\Notifications;

use App\Models\Channel;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChannelAddedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Channel $channel;
    protected Admin $addedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Channel $channel, Admin $addedBy)
    {
        $this->channel = $channel;
        $this->addedBy = $addedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Added to Channel',
            'message' => '<strong>' . e($this->addedBy->name) . '</strong> added you to <strong>' . e($this->channel->name) . '</strong>',
            'channel_id' => $this->channel->id,
            'channel_name' => $this->channel->name,
            'added_by_id' => $this->addedBy->id,
            'added_by_name' => $this->addedBy->name,
            'url' => '/admin/chat?channel=' . $this->channel->id,
            'icon' => 'channel',
            'type' => 'channel_added',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
