<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\Email;
use App\Modules\Email\Models\EmailAccount;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Google\Service\Gmail\Draft;
use Illuminate\Support\Facades\Log;

class EmailComposeService
{
    private GmailAuthService $authService;
    private ?Gmail $gmail = null;

    public function __construct(GmailAuthService $authService)
    {
        $this->authService = $authService;
    }

    private function initGmail(EmailAccount $account)
    {
        $this->authService->setTokenForAccount($account);
        $this->gmail = new Gmail($this->authService->getClient());
    }

    /**
     * Send a new email.
     */
    public function send(EmailAccount $account, array $data)
    {
        $this->initGmail($account);

        $rawMessage = $this->createRawMessage($account, $data);

        $message = new Message();
        $message->setRaw($rawMessage);

        try {
            $sentMessage = $this->gmail->users_messages->send('me', $message);

            // Trigger sync for this message specifically to have it in our DB immediately
            app(GmailSyncService::class)->processMessage($account, $sentMessage->getId());

            return $sentMessage;
        } catch (\Exception $e) {
            Log::error("Gmail Send Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create or update a draft.
     */
    public function saveDraft(EmailAccount $account, array $data, ?string $draftId = null)
    {
        $this->initGmail($account);

        $rawMessage = $this->createRawMessage($account, $data);

        $message = new Message();
        $message->setRaw($rawMessage);

        $draft = new Draft();
        $draft->setMessage($message);

        try {
            if ($draftId) {
                $savedDraft = $this->gmail->users_drafts->update('me', $draftId, $draft);
            } else {
                $savedDraft = $this->gmail->users_drafts->create('me', $draft);
            }

            // Sync the message associated with the draft
            app(GmailSyncService::class)->processMessage($account, $savedDraft->getMessage()->getId());

            return $savedDraft;
        } catch (\Exception $e) {
            Log::error("Gmail Draft Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create RFC822 compliant message and encode it in base64url.
     */
    private function createRawMessage(EmailAccount $account, array $data): string
    {
        $to = $data['to'];
        $subject = $data['subject'] ?? '(No Subject)';
        $body = $data['body'] ?? '';

        $boundary = uniqid('np', true);

        $headers = [
            "From: {$account->email_address}",
            "To: {$to}",
            "Subject: {$subject}",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        ];

        if (!empty($data['cc'])) {
            $headers[] = "Cc: {$data['cc']}";
        }

        if (!empty($data['bcc'])) {
            $headers[] = "Bcc: {$data['bcc']}";
        }

        // Add In-Reply-To and References if this is a reply
        if (!empty($data['thread_id']) && !empty($data['in_reply_to'])) {
            $headers[] = "In-Reply-To: {$data['in_reply_to']}";
            $headers[] = "References: {$data['in_reply_to']}";
            // Thread ID is handled by Gmail if we include it in the Message object, 
            // but for raw we just need the headers.
        }

        $messageText = implode("\r\n", $headers) . "\r\n\r\n";

        // Plain text part
        $messageText .= "--{$boundary}\r\n";
        $messageText .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
        $messageText .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $messageText .= strip_tags($body) . "\r\n\r\n";

        // HTML part
        $messageText .= "--{$boundary}\r\n";
        $messageText .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
        $messageText .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $messageText .= $body . "\r\n\r\n";

        $messageText .= "--{$boundary}--";

        return strtr(base64_encode($messageText), ['+' => '-', '/' => '_', '=' => '']);
    }
}
