<?php

namespace App\Modules\Email\Commands;

use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Services\GmailSyncService;
use App\Modules\Email\Services\AuditLogger;
use Illuminate\Console\Command;

class SyncGmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:sync {--account= : The ID of the account to sync} {--limit= : Max emails to fetch}';

    /**
     * The console command description.
     */
    protected $description = 'Sync Gmail messages for all active accounts';

    /**
     * Execute the console command.
     */
    public function handle(GmailSyncService $syncService, AuditLogger $logger)
    {
        $accountId = $this->option('account');
        $limit = max(1, (int) ($this->option('limit') ?: config('gmail.sync.per_page', 50)));

        $query = EmailAccount::where('is_active', true);
        if ($accountId) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->info('No active email accounts found to sync.');
            return 0;
        }

        foreach ($accounts as $account) {
            $this->info("Syncing account: {$account->email_address}...");
            
            try {
                $stats = $syncService->sync($account, $limit);
                $this->success("Finished {$account->email_address}. Added: {$stats['added']}, Failed: {$stats['failed']}");
                $logger->logSync($account, 'success', $stats);
            } catch (\Exception $e) {
                $this->error("Error syncing {$account->email_address}: " . $e->getMessage());
                $logger->logSync($account, 'error', ['error' => $e->getMessage()]);
            }
        }

        return 0;
    }

    private function success($message)
    {
        $this->output->writeln("<info>✔</info> $message");
    }
}
