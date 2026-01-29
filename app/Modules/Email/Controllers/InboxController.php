<?php

namespace App\Modules\Email\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Repositories\EmailRepository;
use App\Modules\Email\Services\GmailSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InboxController extends Controller
{
    private EmailRepository $repository;
    private GmailSyncService $syncService;

    public function __construct(EmailRepository $repository, GmailSyncService $syncService)
    {
        $this->repository = $repository;
        $this->syncService = $syncService;
    }

    /**
     * List all accounts accessible to the user.
     */
    public function accounts()
    {
        $accounts = Auth::guard('admin')->user()->emailAccounts()->get();
        return view('email::accounts', compact('accounts'));
    }

    /**
     * View inbox for a specific account.
     */
    public function index(EmailAccount $account, Request $request)
    {
        return $this->renderFolder($account, $request, 'inbox');
    }

    /**
     * View sent messages for a specific account.
     */
    public function sent(EmailAccount $account, Request $request)
    {
        return $this->renderFolder($account, $request, 'sent');
    }

    /**
     * View starred messages for a specific account.
     */
    public function starred(EmailAccount $account, Request $request)
    {
        return $this->renderFolder($account, $request, 'starred');
    }

    /**
     * View draft messages for a specific account.
     */
    public function drafts(EmailAccount $account, Request $request)
    {
        return $this->renderFolder($account, $request, 'drafts');
    }

    /**
     * View trash messages for a specific account.
     */
    public function trash(EmailAccount $account, Request $request)
    {
        return $this->renderFolder($account, $request, 'trash');
    }

    /**
     * Common method to render email list folders.
     */
    private function renderFolder(EmailAccount $account, Request $request, string $folder)
    {
        $this->authorize('viewAccount', $account);

        $userId = Auth::guard('admin')->id();
        $query = $request->get('q');

        if ($query) {
            $emails = $this->repository->search($account, $userId, $query, $folder);
        } else {
            switch ($folder) {
                case 'sent':
                    $emails = $this->repository->getSent($account, $userId);
                    break;
                case 'starred':
                    $emails = $this->repository->getStarred($account, $userId);
                    break;
                case 'drafts':
                    $emails = $this->repository->getDrafts($account, $userId);
                    break;
                case 'trash':
                    $emails = $this->repository->getTrash($account, $userId);
                    break;
                default:
                    $emails = $this->repository->getInbox($account, $userId);
            }
        }

        $folderTitle = ucfirst($folder);
        return view('email::index', compact('account', 'emails', 'folder', 'folderTitle'));
    }

    /**
     * View a single email message.
     */
    public function show(EmailAccount $account, int $id)
    {
        $this->authorize('viewAccount', $account);

        $userId = Auth::guard('admin')->id();
        $email = $this->repository->findWithDetails($id, $userId);

        if (!$email || $email->email_account_id !== $account->id) {
            abort(404);
        }

        // Mark as read automatically
        $email->userStates()->updateOrCreate(
            ['user_id' => $userId],
            ['is_read' => true, 'read_at' => now()]
        );

        return view('email::show', compact('account', 'email'));
    }

    /**
     * Manual sync trigger.
     */
    public function sync(EmailAccount $account)
    {
        $this->authorize('viewAccount', $account);

        // Extend execution time for sync operations (5 minutes)
        set_time_limit(300);

        try {
            $stats = $this->syncService->sync($account);
            return back()->with('success', "Synced successfully: {$stats['added']} new emails.");
        } catch (\Exception $e) {
            return back()->with('error', "Sync failed: " . $e->getMessage());
        }
    }
}
