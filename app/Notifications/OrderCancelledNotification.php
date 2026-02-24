<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $order;
    public $admin;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $admin)
    {
        $this->order = $order;
        $this->admin = $admin;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Defaulting to database for admin system
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'attention',
            'title' => 'Order Cancelled',
            'message' => "Order #{$this->order->id} for {$this->order->client_name} has been cancelled by {$this->admin->name}.",
            'details' => [
                'order_id' => $this->order->id,
                'client_name' => $this->order->client_name,
                'cancelled_by_id' => $this->admin->id,
                'cancelled_by_name' => $this->admin->name,
                'reason' => $this->order->cancel_reason,
            ],
            'url' => route('orders.show', $this->order->id)
        ];
    }
}
