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
    protected Order $order;
    protected Admin $updatedBy;
    protected array $oldValues;
    protected array $newValues;

    /**
     * Create a new notification instance.
     * @param Order $order
     * @param Admin $updatedBy
     * @param array $oldValues Old values of changed fields
     * @param array $newValues New values of changed fields
     */
    public function __construct(Order $order, Admin $updatedBy, array $oldValues = [], array $newValues = [])
    {
        $this->order = $order;
        $this->updatedBy = $updatedBy;
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;

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
        $changeStrings = [];
        foreach ($this->newValues as $field => $newVal) {
            $oldVal = $this->oldValues[$field] ?? 'N/A';

            // Limit string length for long text fields
            $oldValStr = is_string($oldVal) && strlen($oldVal) > 20 ? substr($oldVal, 0, 17) . '...' : (string) $oldVal;
            $newValStr = is_string($newVal) && strlen($newVal) > 20 ? substr($newVal, 0, 17) . '...' : (string) $newVal;

            $changeStrings[] = "{$field} ({$oldValStr} → {$newValStr})";
        }

        $fieldList = implode(', ', $changeStrings);

        return [
            'title' => 'Order Updated',
            'message' => "Order #{$this->order->id} was updated by {$this->updatedBy->name}. Changes: " . $fieldList,
            'order_id' => $this->order->id,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'changes' => [
                'old' => $this->oldValues,
                'new' => $this->newValues
            ],
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
