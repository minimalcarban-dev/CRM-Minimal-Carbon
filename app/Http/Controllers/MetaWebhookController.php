<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MetaAccount;
use App\Models\MetaConversation;
use App\Models\MetaMessage;
use App\Services\MetaApiService;
use App\Services\LeadScoringService;
use App\Services\LeadAssignmentService;
use App\Jobs\ProcessMetaWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    /**
     * Verify webhook subscription from Meta
     */
    public function verify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $verifyToken = config('services.meta.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::channel('meta')->info('Webhook verified successfully');
            return response($challenge, 200);
        }

        Log::channel('meta')->warning('Webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook events from Meta
     */
    public function handle(Request $request)
    {
        // Log the incoming webhook immediately (for debugging)
        Log::channel('meta')->info('Webhook POST received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'raw' => substr($request->getContent(), 0, 500),
        ]);

        // Verify signature
        $signature = $request->header('X-Hub-Signature-256', '');
        $payload = $request->getContent();

        // Log signature check details
        Log::channel('meta')->info('Signature check', [
            'has_signature' => !empty($signature),
            'signature_prefix' => substr($signature, 0, 20),
        ]);

        if (!MetaApiService::verifyWebhookSignature($payload, $signature)) {
            Log::channel('meta')->warning('Invalid webhook signature', [
                'signature' => $signature,
                'app_secret_configured' => !empty(config('services.meta.app_secret')),
            ]);
            // For testing: still process even if signature fails (remove in production)
            if (config('app.debug')) {
                Log::channel('meta')->info('DEBUG MODE: Processing despite invalid signature');
            } else {
                return response('Invalid signature', 401);
            }
        }

        // Process synchronously (no queue worker needed - works on cPanel)
        // Meta requires 200 response within 20 seconds, so we process inline
        try {
            self::processPayload($request->all());
        } catch (\Exception $e) {
            Log::channel('meta')->error('Webhook processing error', [
                'error' => $e->getMessage(),
            ]);
            // Still return 200 to prevent Meta from retrying
        }

        // Return 200 immediately as required by Meta
        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Process webhook payload (called from job)
     */
    public static function processPayload(array $payload): void
    {
        $events = MetaApiService::parseWebhookPayload($payload);

        foreach ($events as $event) {
            try {
                match ($event['type']) {
                    'message' => self::handleMessageEvent($event),
                    'delivery' => self::handleDeliveryEvent($event),
                    'read' => self::handleReadEvent($event),
                    default => null,
                };
            } catch (\Exception $e) {
                Log::channel('meta')->error('Error processing webhook event', [
                    'event' => $event,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle incoming message event
     */
    protected static function handleMessageEvent(array $event): void
    {
        // Skip echo messages (our own outgoing messages)
        if ($event['is_echo'] ?? false) {
            return;
        }

        $senderId = $event['sender_id'];
        $recipientId = $event['recipient_id'];
        $messageId = $event['message_id'];
        $text = $event['text'];
        $attachments = $event['attachments'];

        // Find or create lead
        $lead = Lead::firstOrCreate(
            ['platform_user_id' => $senderId],
            [
                'name' => 'Unknown User', // Will be updated from profile
                'platform' => $event['platform'] ?? 'facebook', // Detect from webhook payload
                'status' => 'new',
                'priority' => 'medium',
                'first_contact_at' => now(),
                'sla_deadline' => now()->addHours(config('leads.default_sla_hours', 24)),
            ]
        );

        // Try to fetch user profile
        if ($lead->name === 'Unknown User') {
            self::fetchAndUpdateUserProfile($lead, $senderId, $recipientId);
        }

        // Find or create conversation
        $metaAccount = MetaAccount::where('page_id', $recipientId)
            ->orWhere('account_id', $recipientId)
            ->first();

        if (!$metaAccount) {
            Log::channel('meta')->warning('Meta account not found', ['recipient_id' => $recipientId]);
            return;
        }

        $conversation = MetaConversation::firstOrCreate(
            ['conversation_id' => "{$senderId}_{$recipientId}"],
            [
                'lead_id' => $lead->id,
                'meta_account_id' => $metaAccount->id,
                'platform' => $lead->platform,
            ]
        );

        // Check for duplicate message
        if (MetaMessage::where('message_id', $messageId)->exists()) {
            Log::channel('meta')->info('Duplicate message ignored', ['message_id' => $messageId]);
            return;
        }

        // Create message
        $message = $conversation->messages()->create([
            'message_id' => $messageId,
            'direction' => MetaMessage::DIRECTION_INCOMING,
            'content' => $text,
            'attachments' => $attachments,
            'status' => MetaMessage::STATUS_DELIVERED,
            'sender_id' => $senderId,
        ]);

        // Update conversation and lead
        $conversation->update([
            'last_message_at' => now(),
            'is_read' => false,
        ]);

        $lead->update(['last_contact_at' => now()]);

        // Log activity
        $lead->logActivity(
            LeadActivity::TYPE_MESSAGE_RECEIVED,
            substr($text ?? 'Attachment received', 0, 100),
            null,
            ['message_id' => $message->id]
        );

        // Update lead score
        app(LeadScoringService::class)->updateScore($lead);

        // Auto-assign if new lead
        if ($lead->status === 'new' && !$lead->assigned_to && config('leads.auto_assign', true)) {
            app(LeadAssignmentService::class)->assignLead($lead);
        }

        // Broadcast real-time notification via Pusher
        event(new \App\Events\NewLeadMessage($lead, $message));

        Log::channel('meta')->info('Message processed successfully', [
            'lead_id' => $lead->id,
            'message_id' => $message->id,
        ]);
    }

    /**
     * Handle delivery status event
     */
    protected static function handleDeliveryEvent(array $event): void
    {
        $messageIds = $event['message_ids'] ?? [];

        MetaMessage::whereIn('message_id', $messageIds)
            ->where('status', '!=', MetaMessage::STATUS_READ)
            ->update(['status' => MetaMessage::STATUS_DELIVERED]);
    }

    /**
     * Handle read receipt event
     */
    protected static function handleReadEvent(array $event): void
    {
        $watermark = $event['watermark'];

        // Mark all messages sent before watermark as read
        // Watermark is a timestamp in milliseconds
        $timestamp = \Carbon\Carbon::createFromTimestampMs($watermark);

        MetaMessage::where('direction', MetaMessage::DIRECTION_OUTGOING)
            ->where('created_at', '<=', $timestamp)
            ->where('status', '!=', MetaMessage::STATUS_READ)
            ->update([
                'status' => MetaMessage::STATUS_READ,
                'read_at' => now(),
            ]);
    }

    /**
     * Detect platform from webhook payload
     */
    protected static function detectPlatform(array $payload): string
    {
        $object = $payload['object'] ?? 'page';

        return match ($object) {
            'instagram' => 'instagram',
            'page', 'facebook' => 'facebook',
            default => 'facebook',
        };
    }

    /**
     * Fetch user profile from Meta and update lead
     */
    protected static function fetchAndUpdateUserProfile(Lead $lead, string $userId, string $pageId): void
    {
        try {
            $metaAccount = MetaAccount::where('page_id', $pageId)
                ->orWhere('account_id', $pageId)
                ->first();

            if (!$metaAccount) {
                return;
            }

            $api = new MetaApiService();
            $api->setAccount($metaAccount);

            $profile = $api->getUserProfile($userId);

            if ($profile) {
                $lead->update([
                    'name' => $profile['name'] ?? 'Unknown User',
                    'profile_pic_url' => $profile['profile_pic'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('meta')->warning('Failed to fetch user profile', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
