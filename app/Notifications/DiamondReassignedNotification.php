<?php

namespace App\Notifications;

use App\Models\Diamond;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiamondReassignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Diamond $diamond;
    protected $reassignedBy;
    protected $previousAdmin;

    /**
     * Create a new notification instance.
     */
    public function __construct(Diamond $diamond, $reassignedBy, $previousAdmin)
    {
        $this->diamond = $diamond;
        $this->reassignedBy = $reassignedBy;
        $this->previousAdmin = $previousAdmin;
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
            ->greeting('Hello ' . $this->previousAdmin->name . ',')
            ->line('A diamond previously assigned to you has been reassigned.')
            ->line('**Diamond Details:**')
            ->line('SKU: ' . $this->diamond->sku)
            ->line('Stock ID: ' . $this->diamond->stockid)
            ->line('Price: ' . $this->diamond->price)
            ->line('Type: ' . ($this->diamond->diamond_type ?? 'N/A'))
            ->line('Reassigned by: ' . $this->reassignedBy->name)
            ->line('Reassigned to: ' . ($notifiable->name ?? 'Unknown'))
            ->action('View Diamond', route('diamond.show', $this->diamond->id))
            ->line('Thank you for using our system!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Diamond Reassigned',
            'diamond_id' => $this->diamond->id,
            'diamond_sku' => $this->diamond->sku,
            'diamond_price' => $this->diamond->price,
            'reassigned_by' => $this->reassignedBy->name,
            'message' => 'Diamond ' . $this->diamond->sku . ' has been reassigned by ' . $this->reassignedBy->name,
            'details' => [
                'sku' => $this->diamond->sku,
                'stock_id' => $this->diamond->stockid,
                'price' => 'Rs. ' . number_format((float)$this->diamond->price, 2),
                'type' => $this->diamond->diamond_type ?? 'N/A',
                'reassigned_by' => $this->reassignedBy->name,
                'previous_admin' => $this->previousAdmin->name,
            ]
        ];
    }
}
