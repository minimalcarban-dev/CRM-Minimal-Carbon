<?php

namespace App\Modules\Email\Repositories;

use App\Modules\Email\Models\Email;
use App\Modules\Email\Models\EmailAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmailRepository
{
    /**
     * Get paginated emails for an account grouped by thread.
     * Shows threads that have at least one message in the INBOX.
     */
    public function getInbox(EmailAccount $account, int $userId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->getGroupedThreads($account, $userId, function (Builder $query) {
            $query->whereJsonContains('labels', 'INBOX');
        }, $perPage);
    }

    /**
     * Get paginated sent emails for an account grouped by thread.
     */
    public function getSent(EmailAccount $account, int $userId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->getGroupedThreads($account, $userId, function (Builder $query) {
            $query->whereJsonContains('labels', 'SENT');
        }, $perPage);
    }

    /**
     * Get paginated draft emails for an account grouped by thread.
     */
    public function getDrafts(EmailAccount $account, int $userId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->getGroupedThreads($account, $userId, function (Builder $query) {
            $query->whereJsonContains('labels', 'DRAFT');
        }, $perPage);
    }

    /**
     * Get paginated trash emails for an account grouped by thread.
     */
    public function getTrash(EmailAccount $account, int $userId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->getGroupedThreads($account, $userId, function (Builder $query) {
            $query->whereJsonContains('labels', 'TRASH');
        }, $perPage);
    }

    /**
     * Get paginated starred emails for an account grouped by thread.
     */
    public function getStarred(EmailAccount $account, int $userId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->getGroupedThreads($account, $userId, function (Builder $query) use ($userId) {
            $query->whereHas('userStates', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('is_starred', true);
            });
        }, $perPage);
    }

    /**
     * Generic method to get grouped threads with a custom filter.
     */
    private function getGroupedThreads(EmailAccount $account, int $userId, callable $filter, int $perPage = 50): LengthAwarePaginator
    {
        // Get threads that match the filter
        $matchingThreadIds = Email::select('thread_id')
            ->where('email_account_id', $account->id)
            ->whereNull('deleted_at')
            ->where($filter)
            ->distinct()
            ->pluck('thread_id');

        // Get the latest email ID for each matching thread
        $latestEmailsSubquery = Email::select('thread_id', DB::raw('MAX(id) as latest_id'))
            ->where('email_account_id', $account->id)
            ->whereIn('thread_id', $matchingThreadIds)
            ->whereNull('deleted_at')
            ->groupBy('thread_id');

        return Email::where('email_account_id', $account->id)
            ->joinSub($latestEmailsSubquery, 'latest_emails', function ($join) {
                $join->on('emails.id', '=', 'latest_emails.latest_id');
            })
            ->with([
                'userStates' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->withCount([
                'threadMessages as thread_count' => function ($query) use ($account) {
                    $query->where('email_account_id', $account->id);
                }
            ])
            ->orderBy('received_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search emails across account (grouped by thread), optionally filtered by folder.
     */
    public function search(EmailAccount $account, int $userId, string $query, ?string $folder = null, int $perPage = 50): LengthAwarePaginator
    {
        // Get the threads that have messages matching the search criteria
        $matchingThreadsQuery = Email::select('thread_id')
            ->where('email_account_id', $account->id)
            ->where(function (Builder $q) use ($query) {
                $q->where('subject', 'like', "%{$query}%")
                    ->orWhere('from_name', 'like', "%{$query}%")
                    ->orWhere('from_email', 'like', "%{$query}%")
                    ->orWhere('to_recipients', 'like', "%{$query}%")
                    ->orWhere('body_plain', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at');

        // Apply folder filter if provided
        if ($folder) {
            switch ($folder) {
                case 'sent':
                    $matchingThreadsQuery->whereJsonContains('labels', 'SENT');
                    break;
                case 'drafts':
                    $matchingThreadsQuery->whereJsonContains('labels', 'DRAFT');
                    break;
                case 'trash':
                    $matchingThreadsQuery->whereJsonContains('labels', 'TRASH');
                    break;
                case 'starred':
                    $matchingThreadsQuery->whereHas('userStates', function ($q) use ($userId) {
                        $q->where('user_id', $userId)->where('is_starred', true);
                    });
                    break;
                case 'inbox':
                default:
                    if ($folder === 'inbox') {
                        $matchingThreadsQuery->whereJsonContains('labels', 'INBOX');
                    }
                    break;
            }
        }

        $matchingThreads = $matchingThreadsQuery->distinct()->pluck('thread_id');

        // Get the latest email from each matching thread
        $latestEmailsSubquery = Email::select('thread_id', DB::raw('MAX(id) as latest_id'))
            ->where('email_account_id', $account->id)
            ->whereIn('thread_id', $matchingThreads)
            ->whereNull('deleted_at')
            ->groupBy('thread_id');

        return Email::where('email_account_id', $account->id)
            ->joinSub($latestEmailsSubquery, 'latest_emails', function ($join) {
                $join->on('emails.id', '=', 'latest_emails.latest_id');
            })
            ->with([
                'userStates' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->withCount([
                'threadMessages as thread_count' => function ($query) use ($account) {
                    $query->where('email_account_id', $account->id);
                }
            ])
            ->orderBy('received_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a single email with attachments and user state.
     */
    public function findWithDetails(int $id, int $userId): ?Email
    {
        return Email::with([
            'attachments',
            'userStates' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        ])->find($id);
    }

    /**
     * Get all emails in a thread (conversation view).
     */
    public function getThreadEmails(EmailAccount $account, string $threadId, int $userId)
    {
        return Email::where('email_account_id', $account->id)
            ->where('thread_id', $threadId)
            ->with([
                'attachments',
                'userStates' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->orderBy('received_at', 'asc')
            ->get();
    }
}
