<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderDiscussionNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected Order $order;
    protected Admin $postedBy;
    protected string $messagePreview;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, Admin $postedBy, string $messagePreview = '')
    {
        $this->order = $order;
        $this->postedBy = $postedBy;
        $this->messagePreview = $messagePreview;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        $preview = $this->messagePreview;
        if (strlen($preview) > 80) {
            $preview = substr($preview, 0, 77) . '...';
        }

        return [
            'title' => 'Order Discussion Update',
            'message' => "Order #{$this->order->id}: New message by {$this->postedBy->name}",
            'message_preview' => $preview,
            'order_id' => $this->order->id,
            'posted_by' => $this->postedBy->name,
            'posted_by_id' => $this->postedBy->id,
            'url' => route('orders.show', $this->order->id),
            'type' => 'order_discussion',
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
