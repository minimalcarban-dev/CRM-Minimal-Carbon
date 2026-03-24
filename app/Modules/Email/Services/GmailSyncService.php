<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\Email;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Models\EmailAttachment;
use App\Modules\Email\Models\EmailUserState;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Gmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GmailSyncService
{
    private GmailAuthService $authService;

    public function __construct(GmailAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Initial full sync or incremental sync.
     */
    public function sync(EmailAccount $account, int $maxEmails = 50): array
    {
        set_time_limit(300);

        $limit = max(1, $maxEmails);
        $account->update(['sync_status' => 'syncing', 'sync_error' => null]);

        try {
            if ($account->history_id) {
                return $this->incrementalSync($account, $limit);
            }

            return $this->fullSync($account, $limit);
        } catch (\Throwable $e) {
            $account->update(['sync_status' => 'error', 'sync_error' => $e->getMessage()]);
            Log::error("Gmail Sync Error [{$account->email_address}]: " . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Move a message to trash in Gmail.
     */
    public function trashMessage(EmailAccount $account, string $messageId)
    {
        $gmail = $this->gmailForAccount($account);

        return $gmail->users_messages->trash('me', $messageId);
    }

    /**
     * Fetch, parse and store a single message.
     */
    public function processMessage(EmailAccount $account, string $messageId): Email
    {
        $gmail = $this->gmailForAccount($account);
        $googleMsg = $gmail->users_messages->get('me', $messageId, ['format' => 'full']);
        $payload = $googleMsg->getPayload();
        $headers = $this->parseHeaders($payload->getHeaders() ?? []);

        return DB::transaction(function () use ($account, $messageId, $googleMsg, $headers, $payload) {
            $email = Email::withTrashed()->updateOrCreate(
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
                    'labels' => $googleMsg->getLabelIds() ?? [],
                    'headers' => $headers,
                ]
            );

            if ($email->trashed()) {
                $email->restore();
            }

            $this->processAttachments($email, $payload);
            $this->syncUserStates($email, $googleMsg->getLabelIds() ?? []);

            return $email->fresh(['account.users']);
        });
    }

    /**
     * Perform a full sync across key Gmail system folders.
     */
    private function fullSync(EmailAccount $account, int $limit): array
    {
        $gmail = $this->gmailForAccount($account);
        $stats = ['added' => 0, 'updated' => 0, 'failed' => 0, 'deleted' => 0];
        $labelsToSync = ['INBOX', 'SENT', 'TRASH', 'DRAFT'];

        foreach ($labelsToSync as $label) {
            try {
                $messagesResponse = $gmail->users_messages->listUsersMessages('me', [
                    'maxResults' => min($limit, 500),
                    'labelIds' => [$label],
                ]);

                $messageIds = collect($messagesResponse->getMessages() ?? [])
                    ->map(fn($message) => $message->getId())
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $this->syncMessageIds($account, $messageIds, $stats);
            } catch (\Throwable $e) {
                Log::error("Failed to sync label {$label} for {$account->email_address}: " . $e->getMessage());
            }
        }

        $this->markSyncComplete($account, $this->getLatestHistoryId($account));

        return $stats;
    }

    /**
     * Perform an incremental sync using Gmail History API.
     */
    private function incrementalSync(EmailAccount $account, int $limit): array
    {
        $gmail = $this->gmailForAccount($account);
        $stats = ['added' => 0, 'updated' => 0, 'failed' => 0, 'deleted' => 0];
        $upsertMessageIds = [];
        $deletedMessageIds = [];
        $pageToken = null;
        $latestHistoryId = $account->history_id;

        try {
            do {
                $params = [
                    'startHistoryId' => $account->history_id,
                    'maxResults' => min($limit, 500),
                ];

                if ($pageToken) {
                    $params['pageToken'] = $pageToken;
                }

                $response = $gmail->users_history->listUsersHistory('me', $params);

                foreach ($response->getHistory() ?? [] as $history) {
                    $latestHistoryId = $history->getId() ?: $latestHistoryId;

                    foreach ($history->getMessagesAdded() ?? [] as $item) {
                        $message = $item->getMessage();
                        if ($message && $message->getId()) {
                            $upsertMessageIds[] = $message->getId();
                        }
                    }

                    foreach ($history->getLabelsAdded() ?? [] as $item) {
                        $message = $item->getMessage();
                        if ($message && $message->getId()) {
                            $upsertMessageIds[] = $message->getId();
                        }
                    }

                    foreach ($history->getLabelsRemoved() ?? [] as $item) {
                        $message = $item->getMessage();
                        if ($message && $message->getId()) {
                            $upsertMessageIds[] = $message->getId();
                        }
                    }

                    foreach ($history->getMessagesDeleted() ?? [] as $item) {
                        $message = $item->getMessage();
                        if ($message && $message->getId()) {
                            $deletedMessageIds[] = $message->getId();
                        }
                    }
                }

                $pageToken = $response->getNextPageToken();
                $latestHistoryId = $response->getHistoryId() ?: $latestHistoryId;
            } while ($pageToken);
        } catch (GoogleServiceException $e) {
            if ((int) $e->getCode() === 404) {
                return $this->fullSync($account, $limit);
            }

            throw $e;
        }

        $deletedMessageIds = array_values(array_unique(array_filter($deletedMessageIds)));
        $upsertMessageIds = array_values(array_diff(array_unique(array_filter($upsertMessageIds)), $deletedMessageIds));

        $this->deleteLocalMessages($account, $deletedMessageIds, $stats);
        $this->syncMessageIds($account, $upsertMessageIds, $stats);
        $this->markSyncComplete($account, $latestHistoryId ?: $this->getLatestHistoryId($account));

        return $stats;
    }

    private function syncMessageIds(EmailAccount $account, array $messageIds, array &$stats): void
    {
        foreach ($messageIds as $messageId) {
            $exists = Email::withTrashed()
                ->where('email_account_id', $account->id)
                ->where('message_id', $messageId)
                ->exists();

            try {
                $this->processMessage($account, $messageId);
                $stats[$exists ? 'updated' : 'added']++;
            } catch (\Throwable $e) {
                Log::warning("Failed to process Gmail message {$messageId} for {$account->email_address}: " . $e->getMessage());
                $stats['failed']++;
            }
        }
    }

    private function deleteLocalMessages(EmailAccount $account, array $messageIds, array &$stats): void
    {
        if ($messageIds === []) {
            return;
        }

        $emails = Email::where('email_account_id', $account->id)
            ->whereIn('message_id', $messageIds)
            ->get();

        foreach ($emails as $email) {
            if (!$email->trashed()) {
                $email->delete();
                $stats['deleted']++;
            }
        }
    }

    private function markSyncComplete(EmailAccount $account, ?string $historyId): void
    {
        $account->update([
            'sync_status' => 'idle',
            'sync_error' => null,
            'last_sync_at' => now(),
            'history_id' => $historyId,
        ]);
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
    private function parseBody($part, string $mimeType): ?string
    {
        if ($part->getMimeType() === $mimeType) {
            $data = $part->getBody()?->getData();

            if (!$data) {
                return null;
            }

            return base64_decode(strtr($data, '-_', '+/')) ?: null;
        }

        foreach ($part->getParts() ?? [] as $subPart) {
            $content = $this->parseBody($subPart, $mimeType);
            if ($content !== null) {
                return $content;
            }
        }

        return null;
    }

    /**
     * Extract attachments metadata.
     */
    private function processAttachments(Email $email, $payload): void
    {
        $parts = $this->flattenParts($payload);

        foreach ($parts as $part) {
            if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
                $headers = $this->parseHeaders($part->getHeaders() ?? []);

                EmailAttachment::updateOrCreate(
                    ['email_id' => $email->id, 'attachment_id' => $part->getBody()->getAttachmentId()],
                    [
                        'filename' => $part->getFilename(),
                        'content_type' => $part->getMimeType(),
                        'size_bytes' => $part->getBody()->getSize(),
                        'is_inline' => !empty($headers['content-id']),
                        'content_id' => $headers['content-id'] ?? null,
                    ]
                );

                if (!$email->has_attachments) {
                    $email->update(['has_attachments' => true]);
                }
            }
        }
    }

    private function flattenParts($part, array &$parts = []): array
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

    private function syncUserStates(Email $email, array $labels): void
    {
        $email->loadMissing('account.users');

        $isUnread = in_array('UNREAD', $labels, true);
        $isStarred = in_array('STARRED', $labels, true);

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

    private function gmailForAccount(EmailAccount $account): Gmail
    {
        $this->authService->setTokenForAccount($account);

        return new Gmail($this->authService->getClient());
    }

    private function getLatestHistoryId(EmailAccount $account): ?string
    {
        try {
            $gmail = $this->gmailForAccount($account);
            $profile = $gmail->users->getProfile('me');

            return $profile->getHistoryId();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
