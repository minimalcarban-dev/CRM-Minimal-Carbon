<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderProductivityReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Reminder',
            'message' => "Hey {$notifiable->name}, do you have any new orders to enter? Don't forget to log them in the system!",
            'type' => 'order_reminder',
            'url' => route('orders.create'),
            'icon' => 'bi-cart-plus',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Order Reminder',
            'message' => "Hey {$notifiable->name}, do you have any new orders to enter? Don't forget to log them in the system!",
            'type' => 'order_reminder',
            'url' => route('orders.create'),
            'icon' => 'bi-cart-plus',
            'show_toast' => true,
        ]);
    }
}
