<?php

namespace App\Notifications;

use App\Models\Diamond;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class DiamondSoldNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected Diamond $diamond;
    protected Admin $soldBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Diamond $diamond, Admin $soldBy)
    {
        $this->diamond = $diamond;
        $this->soldBy = $soldBy;
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
            'title' => 'Diamond Sold',
            'message' => "Diamond {$this->diamond->sku} has been sold by {$this->soldBy->name}",
            'diamond_id' => $this->diamond->id,
            'diamond_sku' => $this->diamond->sku,
            'sold_price' => $this->diamond->sold_out_price ?? 0,
            'sold_by' => $this->soldBy->name,
            'sold_by_id' => $this->soldBy->id,
            'url' => route('diamond.show', $this->diamond->id),
            'type' => 'diamond_sold',
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
