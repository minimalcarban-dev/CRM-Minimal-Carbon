<?php

namespace App\Jobs;

use App\Models\MetaMessage;
use App\Models\MetaMessageLog;
use App\Models\MetaConversation;
use App\Services\MetaApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMetaMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected int $conversationId;
    protected string $content;
    protected ?array $attachment;

    /**
     * Create a new job instance.
     */
    public function __construct(int $conversationId, string $content, ?array $attachment = null)
    {
        $this->conversationId = $conversationId;
        $this->content = $content;
        $this->attachment = $attachment;
        $this->onQueue('messages');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $conversation = MetaConversation::with(['lead', 'metaAccount'])->find($this->conversationId);

        if (!$conversation) {
            Log::channel('meta')->error('Conversation not found', ['id' => $this->conversationId]);
            return;
        }

        $lead = $conversation->lead;
        $metaAccount = $conversation->metaAccount;

        if (!$metaAccount || !$metaAccount->is_active) {
            Log::channel('meta')->error('Meta account not active', ['id' => $metaAccount?->id]);
            return;
        }

        // Create pending message record
        $message = $conversation->messages()->create([
            'message_id' => 'pending_' . uniqid(),
            'direction' => MetaMessage::DIRECTION_OUTGOING,
            'content' => $this->content,
            'attachments' => $this->attachment ? [$this->attachment] : null,
            'status' => MetaMessage::STATUS_PENDING,
        ]);

        // Send via API
        $api = new MetaApiService();
        $api->setAccount($metaAccount);

        $result = $this->attachment
            ? $api->sendAttachment($lead->platform_user_id, $this->attachment['type'], $this->attachment['url'])
            : $api->sendMessage($lead->platform_user_id, $this->content, $lead->platform);

        // Log the attempt
        MetaMessageLog::create([
            'meta_message_id' => $message->id,
            'event_type' => $result['success'] ? 'sent' : 'failed',
            'api_response' => json_encode($result['response'] ?? []),
            'retry_count' => $this->attempts(),
            'error_message' => $result['error'] ?? null,
        ]);

        if ($result['success']) {
            $message->update([
                'message_id' => $result['message_id'],
                'status' => MetaMessage::STATUS_SENT,
            ]);

            // Update conversation
            $conversation->updateLastMessageTime();

            // Update lead
            $lead->update(['last_contact_at' => now()]);
        } else {
            if ($this->attempts() >= $this->tries) {
                $message->markAsFailed();
            }
            throw new \Exception($result['error'] ?? 'Failed to send message');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('meta')->error('SendMetaMessage job failed', [
            'conversation_id' => $this->conversationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
