<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\Email;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Models\EmailAttachment;
use App\Modules\Email\Models\EmailUserState;
use Google\Service\Gmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GmailSyncService
{
    private GmailAuthService $authService;
    private ?Gmail $gmail = null;

    public function __construct(GmailAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Initial full sync or incremental sync.
     */
    public function sync(EmailAccount $account, int $maxEmails = 50): array
    {
        // Extend execution time for sync operations (5 minutes)
        set_time_limit(300);

        $this->ensureGmailInitialized($account);

        $account->update(['sync_status' => 'syncing', 'sync_error' => null]);

        try {
            if ($account->history_id) {
                return $this->incrementalSync($account);
            } else {
                return $this->fullSync($account, $maxEmails);
            }
        } catch (\Exception $e) {
            $account->update(['sync_status' => 'error', 'sync_error' => $e->getMessage()]);
            Log::error("Gmail Sync Error [{$account->email_address}]: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Perform a full sync of the inbox.
     */
    private function fullSync(EmailAccount $account, int $limit): array
    {
        $stats = ['added' => 0, 'updated' => 0, 'failed' => 0];

        $labelsToSync = ['INBOX', 'SENT', 'TRASH', 'DRAFT'];

        foreach ($labelsToSync as $label) {
            try {
                $messagesResponse = $this->gmail->users_messages->listUsersMessages('me', [
                    'maxResults' => $limit,
                    'labelIds' => [$label]
                ]);

                if ($messagesResponse->getMessages()) {
                    $existingMessageIds = Email::where('email_account_id', $account->id)
                        ->whereIn('message_id', array_map(fn($m) => $m->getId(), $messagesResponse->getMessages()))
                        ->pluck('message_id')
                        ->toArray();

                    foreach ($messagesResponse->getMessages() as $msg) {
                        if (in_array($msg->getId(), $existingMessageIds)) {
                            // Optionally sync labels/state if needed even if exists, 
                            // but for speed we skip fully existing
                            $stats['updated']++;
                            continue;
                        }

                        try {
                            $this->processMessage($account, $msg->getId());
                            $stats['added']++;
                        } catch (\Exception $e) {
                            Log::warning("Failed to process message {$msg->getId()}: " . $e->getMessage());
                            $stats['failed']++;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to sync label {$label}: " . $e->getMessage());
            }
        }

        $account->update([
            'sync_status' => 'idle',
            'last_sync_at' => now(),
            'history_id' => $this->getLatestHistoryId($account)
        ]);

        return $stats;
    }

    /**
     * Perform an incremental sync using History API.
     */
    private function incrementalSync(EmailAccount $account): array
    {
        // Simple implementation: fetch latest messages since last history_id
        // A full History API implementation would process each history record (added, labelsRemoved, etc.)
        // For the sake of this build, we fallback to fetching the latest list if history is complex
        return $this->fullSync($account, 50);
    }

    /**
     * Move a message to trash in Gmail.
     */
    public function trashMessage(EmailAccount $account, string $messageId)
    {
        $this->ensureGmailInitialized($account);
        return $this->gmail->users_messages->trash('me', $messageId);
    }

    /**
     * Fetch, parse and store a single message.
     */
    public function processMessage(EmailAccount $account, string $messageId): Email
    {
        $this->ensureGmailInitialized($account);

        $googleMsg = $this->gmail->users_messages->get('me', $messageId, ['format' => 'full']);
        $payload = $googleMsg->getPayload();
        $headers = $this->parseHeaders($payload->getHeaders());

        return DB::transaction(function () use ($account, $messageId, $googleMsg, $headers, $payload) {
            $email = Email::updateOrCreate(
                ['email_account_id' => $account->id, 'message_id' => $messageId],
                [
                    'thread_id' => $googleMsg->getThreadId(),
                    'subject' => $headers['subject'] ?? '(No Subject)',
                    'from_name' => $this->parseFromName($headers['from'] ?? ''),
                    'from_email' => $this->parseFromEmail($headers['from'] ?? ''),
                    'to_recipients' => $headers['to'] ?? null,
                    'cc_recipients' => $headers['cc'] ?? null,
                    'bcc_recipients' => $headers['bcc'] ?? null,
                    'body_html' => $this->parseBody($payload, 'text/html'),
                    'body_plain' => $this->parseBody($payload, 'text/plain'),
                    'received_at' => date('Y-m-d H:i:s', $googleMsg->getInternalDate() / 1000),
                    'size_bytes' => $googleMsg->getSizeEstimate(),
                    'labels' => $googleMsg->getLabelIds(),
                    'headers' => $headers,
                ]
            );

            $this->processAttachments($email, $payload);
            $this->syncUserStates($email, $googleMsg->getLabelIds());

            return $email;
        });
    }

    /**
     * Parse headers into an associative array.
     */
    private function parseHeaders(array $headers): array
    {
        $parsed = [];
        foreach ($headers as $header) {
            $parsed[strtolower($header->getName())] = $header->getValue();
        }
        return $parsed;
    }

    /**
     * Parse the message body.
     */
    private function parseBody($part, $mimeType)
    {
        if ($part->getMimeType() === $mimeType) {
            $data = $part->getBody()->getData();
            return base64_decode(strtr($data, '-_', '+/'));
        }

        if ($part->getParts()) {
            foreach ($part->getParts() as $subPart) {
                if ($content = $this->parseBody($subPart, $mimeType)) {
                    return $content;
                }
            }
        }

        return null;
    }

    /**
     * Extract attachments metadata.
     */
    private function processAttachments(Email $email, $payload)
    {
        $parts = $this->flattenParts($payload);
        foreach ($parts as $part) {
            if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
                EmailAttachment::updateOrCreate(
                    ['email_id' => $email->id, 'attachment_id' => $part->getBody()->getAttachmentId()],
                    [
                        'filename' => $part->getFilename(),
                        'content_type' => $part->getMimeType(),
                        'size_bytes' => $part->getBody()->getSize(),
                        'is_inline' => !empty($this->parseHeaders($part->getHeaders())['content-id']),
                        'content_id' => $this->parseHeaders($part->getHeaders())['content-id'] ?? null,
                    ]
                );
                $email->update(['has_attachments' => true]);
            }
        }
    }

    private function flattenParts($part, &$parts = []): array
    {
        if ($part->getParts()) {
            foreach ($part->getParts() as $subPart) {
                $this->flattenParts($subPart, $parts);
            }
        } else {
            $parts[] = $part;
        }
        return $parts;
    }

    private function parseFromName(string $from): string
    {
        if (preg_match('/(.*)<.*>/', $from, $matches)) {
            return trim($matches[1], ' "');
        }
        return $from;
    }

    private function parseFromEmail(string $from): string
    {
        if (preg_match('/<(.*)>/', $from, $matches)) {
            return $matches[1];
        }
        return $from;
    }

    private function syncUserStates(Email $email, array $labels)
    {
        $isUnread = in_array('UNREAD', $labels);
        $isStarred = in_array('STARRED', $labels);

        // We sync state for all users who have access to this account
        foreach ($email->account->users as $user) {
            EmailUserState::updateOrCreate(
                ['email_id' => $email->id, 'user_id' => $user->id],
                [
                    'is_read' => !$isUnread,
                    'is_starred' => $isStarred,
                    'read_at' => !$isUnread ? now() : null,
                    'starred_at' => $isStarred ? now() : null,
                ]
            );
        }
    }

    private function ensureGmailInitialized(EmailAccount $account): void
    {
        if ($this->gmail === null) {
            $this->authService->setTokenForAccount($account);
            $this->gmail = new Gmail($this->authService->getClient());
        }
    }

    private function getLatestHistoryId(EmailAccount $account): ?string
    {
        try {
            $this->ensureGmailInitialized($account);
            $profile = $this->gmail->users->getProfile('me');
            return $profile->getHistoryId();
        } catch (\Exception $e) {
            return null;
        }
    }
}
