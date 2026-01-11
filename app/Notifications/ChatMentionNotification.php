<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\Admin;
use App\Models\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatMentionNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected Message $message;
    protected Admin $sender;
    protected Channel $channel;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message, Admin $sender, Channel $channel)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->channel = $channel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast']; // Can add 'mail' if you want email notifications
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $messagePreview = strlen($this->message->body) > 100
            ? substr($this->message->body, 0, 100) . '...'
            : $this->message->body;

        return (new MailMessage)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('**' . $this->sender->name . '** mentioned you in **' . $this->channel->name . '**')
            ->line('Message: "' . $messagePreview . '"')
            ->action('View Chat', url('/admin/chat?channel=' . $this->channel->id))
            ->line('Thank you for using our chat system!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $messagePreview = strlen($this->message->body) > 100
            ? substr($this->message->body, 0, 100) . '...'
            : $this->message->body;

        // Highlight mentions in the message preview
        $highlightedPreview = preg_replace(
            '/@(' . preg_quote($notifiable->name, '/') . ')/',
            '<span class="text-primary fw-semibold">@$1</span>',
            $messagePreview
        );

        return [
            'title' => 'You were mentioned',
            'message' => '<strong>' . e($this->sender->name) . '</strong> mentioned you in <strong>' . e($this->channel->name) . '</strong>',
            'channel_id' => $this->channel->id,
            'channel_name' => $this->channel->name,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message_id' => $this->message->id,
            'message_preview' => $highlightedPreview,
            'url' => '/admin/chat?channel=' . $this->channel->id,
            'icon' => 'mention',
            'type' => 'mention',
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
