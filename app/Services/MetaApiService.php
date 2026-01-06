<?php

namespace App\Services;

use App\Models\MetaAccount;
use App\Models\MetaConversation;
use App\Models\MetaMessage;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaApiService
{
    protected string $graphApiUrl = 'https://graph.facebook.com/v18.0';
    protected ?MetaAccount $account = null;

    /**
     * Set the Meta account to use for API calls
     */
    public function setAccount(MetaAccount $account): self
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Send a text message via Meta Graph API
     */
    public function sendMessage(string $recipientId, string $message, string $platform = 'facebook'): array
    {
        if (!$this->account) {
            throw new \Exception('Meta account not set');
        }

        $endpoint = $platform === 'instagram'
            ? "/me/messages"
            : "/{$this->account->page_id}/messages";

        try {
            $response = Http::timeout(30)
                ->withToken($this->account->decrypted_token)
                ->post("{$this->graphApiUrl}{$endpoint}", [
                    'recipient' => ['id' => $recipientId],
                    'message' => ['text' => $message],
                    'messaging_type' => 'RESPONSE',
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['message_id'])) {
                Log::channel('meta')->info('Message sent successfully', [
                    'recipient_id' => $recipientId,
                    'message_id' => $data['message_id'],
                ]);

                return [
                    'success' => true,
                    'message_id' => $data['message_id'],
                    'response' => $data,
                ];
            }

            Log::channel('meta')->error('Failed to send message', [
                'recipient_id' => $recipientId,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'error' => $data['error']['message'] ?? 'Unknown error',
                'response' => $data,
            ];

        } catch (\Exception $e) {
            Log::channel('meta')->error('Exception sending message', [
                'recipient_id' => $recipientId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a message with attachment
     */
    public function sendAttachment(string $recipientId, string $type, string $url): array
    {
        if (!$this->account) {
            throw new \Exception('Meta account not set');
        }

        try {
            $response = Http::timeout(30)
                ->withToken($this->account->decrypted_token)
                ->post("{$this->graphApiUrl}/{$this->account->page_id}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'message' => [
                        'attachment' => [
                            'type' => $type, // image, video, audio, file
                            'payload' => ['url' => $url, 'is_reusable' => true],
                        ],
                    ],
                    'messaging_type' => 'RESPONSE',
                ]);

            $data = $response->json();

            return [
                'success' => $response->successful() && isset($data['message_id']),
                'message_id' => $data['message_id'] ?? null,
                'response' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user profile from Meta
     */
    public function getUserProfile(string $userId): ?array
    {
        if (!$this->account) {
            throw new \Exception('Meta account not set');
        }

        try {
            $response = Http::timeout(15)
                ->withToken($this->account->decrypted_token)
                ->get("{$this->graphApiUrl}/{$userId}", [
                    'fields' => 'id,name,profile_pic,email',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::channel('meta')->error('Failed to get user profile', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Mark a message as seen
     */
    public function markAsSeen(string $recipientId): bool
    {
        if (!$this->account) {
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withToken($this->account->decrypted_token)
                ->post("{$this->graphApiUrl}/{$this->account->page_id}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'sender_action' => 'mark_seen',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Show typing indicator
     */
    public function showTyping(string $recipientId): bool
    {
        if (!$this->account) {
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withToken($this->account->decrypted_token)
                ->post("{$this->graphApiUrl}/{$this->account->page_id}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'sender_action' => 'typing_on',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify webhook signature from Meta
     */
    public static function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $appSecret = config('services.meta.app_secret');

        if (!$appSecret) {
            Log::channel('meta')->warning('Meta app secret not configured');
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse incoming webhook payload
     */
    public static function parseWebhookPayload(array $payload): array
    {
        $messages = [];

        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $messaging = $entry['messaging'] ?? [];

            foreach ($messaging as $event) {
                if (isset($event['message'])) {
                    $messages[] = [
                        'type' => 'message',
                        'sender_id' => $event['sender']['id'] ?? null,
                        'recipient_id' => $event['recipient']['id'] ?? null,
                        'timestamp' => $event['timestamp'] ?? null,
                        'message_id' => $event['message']['mid'] ?? null,
                        'text' => $event['message']['text'] ?? null,
                        'attachments' => $event['message']['attachments'] ?? [],
                        'is_echo' => $event['message']['is_echo'] ?? false,
                    ];
                } elseif (isset($event['delivery'])) {
                    $messages[] = [
                        'type' => 'delivery',
                        'message_ids' => $event['delivery']['mids'] ?? [],
                        'watermark' => $event['delivery']['watermark'] ?? null,
                    ];
                } elseif (isset($event['read'])) {
                    $messages[] = [
                        'type' => 'read',
                        'watermark' => $event['read']['watermark'] ?? null,
                    ];
                }
            }
        }

        return $messages;
    }
}
