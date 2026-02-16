<?php

namespace App\Notifications;

use App\Models\MeleeDiamond;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MeleeLowStockNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected MeleeDiamond $diamond;
    protected int $currentStock;

    public function __construct(MeleeDiamond $diamond, int $currentStock)
    {
        $this->diamond = $diamond;
        $this->currentStock = $currentStock;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $categoryName = $this->diamond->category->name ?? 'Unknown';

        return [
            'title' => 'Melee Stock Low',
            'message' => "{$categoryName} - {$this->diamond->shape} ({$this->diamond->size_label}) stock is low: {$this->currentStock} pcs remaining.",
            'diamond_id' => $this->diamond->id,
            'category' => $categoryName,
            'shape' => $this->diamond->shape,
            'size_label' => $this->diamond->size_label,
            'stock' => $this->currentStock,
            'url' => route('melee.index'),
            'type' => 'melee_low_stock',
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
