<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderUpdatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $order;
    protected $modifier;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, Admin $modifier)
    {
        $this->order = $order;
        $this->modifier = $modifier;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Order Updated',
            'message' => "Order #{$this->order->id} updated by {$this->modifier->name}",
            'order_id' => $this->order->id,
            'modifier_id' => $this->modifier->id,
            'modifier_name' => $this->modifier->name,
            'url' => route('orders.show', $this->order->id),
            'type' => 'order_updated',
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
