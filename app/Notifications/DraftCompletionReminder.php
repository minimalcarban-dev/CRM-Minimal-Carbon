<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DraftCompletionReminder extends Notification
{

    protected int $draftCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $draftCount)
    {
        $this->draftCount = $draftCount;
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
        $message = $this->draftCount === 1
            ? "You have 1 incomplete draft waiting. Complete it before it expires!"
            : "You have {$this->draftCount} incomplete drafts waiting. Complete them before they expire!";

        return [
            'title' => 'Draft Reminder',
            'message' => $message,
            'type' => 'draft_reminder',
            'url' => route('orders.drafts.index'),
            'icon' => 'bi-file-earmark-text',
            'draft_count' => $this->draftCount,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $message = $this->draftCount === 1
            ? "You have 1 incomplete draft waiting. Complete it before it expires!"
            : "You have {$this->draftCount} incomplete drafts waiting. Complete them before they expire!";

        return new BroadcastMessage([
            'title' => 'Draft Reminder',
            'message' => $message,
            'type' => 'draft_reminder',
            'url' => route('orders.drafts.index'),
            'icon' => 'bi-file-earmark-text',
            'draft_count' => $this->draftCount,
            'show_toast' => true,
        ]);
    }
}
