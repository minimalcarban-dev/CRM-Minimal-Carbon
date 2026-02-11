<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OverdueOrderNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected Order $order;
    protected int $daysOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, int $daysOverdue)
    {
        $this->order = $order;
        $this->daysOverdue = $daysOverdue;
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
        $daysText = $this->daysOverdue === 1 ? '1 day' : "{$this->daysOverdue} days";

        return [
            'title' => 'Overdue Order',
            'message' => "Order #{$this->order->id} is overdue by {$daysText}",
            'order_id' => $this->order->id,
            'client_name' => $this->order->client_name,
            'dispatch_date' => $this->order->dispatch_date?->format('Y-m-d'),
            'days_overdue' => $this->daysOverdue,
            'url' => route('orders.show', $this->order->id),
            'type' => 'overdue_order',
            'icon_color' => '#ef4444',
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
