<?php

namespace App\Listeners;

use App\Events\UserMentioned;
use App\Notifications\ChatMentionNotification;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;

class SendChatMentionNotification
{
    /**
     * Handle the event.
     */
    public function handle(UserMentioned $event): void
    {
        Log::info('[MENTION] SendChatMentionNotification listener triggered', [
            'mentioned_admin_id' => $event->mentionedAdminId,
            'message_id' => $event->message->id
        ]);

        try {
            // Get the mentioned admin
            $mentionedAdmin = Admin::find($event->mentionedAdminId);
            
            if (!$mentionedAdmin) {
                Log::warning('Mentioned admin not found', ['admin_id' => $event->mentionedAdminId]);
                return;
            }

            Log::info('[MENTION] Found mentioned admin', ['admin' => $mentionedAdmin->name]);

            // Get the sender and channel from the message
            $message = $event->message;
            $message->load(['sender', 'channel']); // Ensure relationships are loaded
            
            $sender = $message->sender;
            $channel = $message->channel;

            if (!$sender || !$channel) {
                Log::warning('Sender or channel not found for mention notification', [
                    'message_id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'channel_id' => $message->channel_id
                ]);
                return;
            }

            Log::info('[MENTION] Loaded sender and channel', [
                'sender' => $sender->name,
                'channel' => $channel->name
            ]);

            // Don't notify if the user mentioned themselves
            if ($mentionedAdmin->id === $sender->id) {
                Log::info('[MENTION] User mentioned themselves, skipping');
                return;
            }

            // Check if notification already exists for this message and admin
            $existingNotification = $mentionedAdmin->notifications()
                ->where('type', ChatMentionNotification::class)
                ->where('data->message_id', $message->id)
                ->where('notifiable_id', $mentionedAdmin->id)
                ->first();

            if ($existingNotification) {
                Log::info('Notification already exists, skipping', [
                    'mentioned_admin' => $mentionedAdmin->id,
                    'message_id' => $message->id
                ]);
                return;
            }

            // Send the notification
            Log::info('[MENTION] Sending notification to admin', [
                'admin_id' => $mentionedAdmin->id,
                'admin_name' => $mentionedAdmin->name
            ]);

            $mentionedAdmin->notify(new ChatMentionNotification($message, $sender, $channel));
            
            Log::info('[MENTION] Notification sent successfully', [
                'mentioned_admin' => $mentionedAdmin->id,
                'sender' => $sender->id,
                'channel' => $channel->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending mention notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
