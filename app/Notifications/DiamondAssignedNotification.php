<?php

namespace App\Notifications;

use App\Models\Diamond;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiamondAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Diamond $diamond;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Diamond $diamond, $assignedBy)
    {
        $this->diamond = $diamond;
        $this->assignedBy = $assignedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new diamond has been assigned to you.')
            ->line('**Diamond Details:**')
            ->line('SKU: ' . $this->diamond->sku)
            ->line('Stock ID: ' . $this->diamond->stockid)
            ->line('Price: ' . $this->diamond->price)
            ->line('Type: ' . ($this->diamond->diamond_type ?? 'N/A'))
            ->line('Assigned by: ' . $this->assignedBy->name)
            ->action('View Diamond', route('diamond.show', $this->diamond->id))
            ->line('Thank you for using our system!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Diamond Assigned',
            'diamond_id' => $this->diamond->id,
            'diamond_sku' => $this->diamond->sku,
            'diamond_price' => $this->diamond->price,
            'assigned_by' => $this->assignedBy->name,
            'message' => 'Diamond ' . $this->diamond->sku . ' has been assigned to you by ' . $this->assignedBy->name,
            'details' => [
                'sku' => $this->diamond->sku,
                'stock_id' => $this->diamond->stockid,
                'price' => 'Rs. ' . number_format((float) $this->diamond->price, 2),
                'type' => $this->diamond->diamond_type ?? 'N/A',
                'assigned_by' => $this->assignedBy->name,
            ]
        ];
    }
}
