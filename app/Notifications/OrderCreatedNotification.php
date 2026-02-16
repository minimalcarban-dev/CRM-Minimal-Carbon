<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderCreatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected Order $order;
    protected Admin $createdBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, Admin $createdBy)
    {
        $this->order = $order;
        $this->createdBy = $createdBy;
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
        return [
            'title' => 'New Order Created',
            'message' => "Order #{$this->order->id} was created by {$this->createdBy->name}",
            'order_id' => $this->order->id,
            'client_name' => $this->order->display_client_name,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->id,
            'url' => route('orders.show', $this->order->id),
            'type' => 'order_created',
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
