<?php

namespace App\Modules\Email\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Email\Models\Email;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Services\EmailComposeService;
use App\Modules\Email\Services\GmailSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailActionController extends Controller
{
    private EmailComposeService $composeService;
    private GmailSyncService $syncService;

    public function __construct(EmailComposeService $composeService, GmailSyncService $syncService)
    {
        $this->composeService = $composeService;
        $this->syncService = $syncService;
    }

    /**
     * Send a new email.
     */
    public function send(EmailAccount $account, Request $request)
    {
        $this->authorize('manageAccount', $account);

        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'thread_id' => 'nullable|string',
            'in_reply_to' => 'nullable|string',
        ]);

        try {
            $this->composeService->send($account, $validated);
            return response()->json(['success' => true, 'message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save a draft.
     */
    public function draft(EmailAccount $account, Request $request)
    {
        $this->authorize('manageAccount', $account);

        $validated = $request->validate([
            'to' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'draft_id' => 'nullable|string',
        ]);

        try {
            $draft = $this->composeService->saveDraft($account, $validated, $validated['draft_id'] ?? null);
            return response()->json([
                'success' => true,
                'message' => 'Draft saved',
                'draft_id' => $draft->getId()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle starred status for an email.
     */
    public function toggleStar(EmailAccount $account, Email $email)
    {
        $this->authorize('view', $email);

        $userId = Auth::guard('admin')->id();
        $state = $email->userStates()->firstOrCreate(['user_id' => $userId]);

        $state->update([
            'is_starred' => !$state->is_starred,
            'starred_at' => !$state->is_starred ? now() : null
        ]);

        return response()->json([
            'success' => true,
            'is_starred' => $state->is_starred
        ]);
    }

    /**
     * Toggle read/unread status.
     */
    public function toggleRead(EmailAccount $account, Email $email)
    {
        $this->authorize('view', $email);

        $userId = Auth::guard('admin')->id();
        $state = $email->userStates()->firstOrCreate(['user_id' => $userId]);

        $state->update([
            'is_read' => !$state->is_read,
            'read_at' => !$state->is_read ? now() : null
        ]);

        return response()->json(['is_read' => $state->is_read]);
    }

    /**
     * Delete an email (move to trash in Gmail).
     */
    public function destroy(EmailAccount $account, Email $email)
    {
        $this->authorize('manageAccount', $account);

        try {
            // Move to trash in Gmail
            $this->syncService->trashMessage($account, $email->message_id);

            // Update local labels immediately so it moves to Trash folder
            $labels = $email->labels ?? [];
            if (($key = array_search('INBOX', $labels)) !== false) {
                unset($labels[$key]);
            }
            if (!in_array('TRASH', $labels)) {
                $labels[] = 'TRASH';
            }

            $email->update([
                'labels' => array_values($labels)
            ]);

            return response()->json(['success' => true, 'message' => 'Email moved to trash']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
