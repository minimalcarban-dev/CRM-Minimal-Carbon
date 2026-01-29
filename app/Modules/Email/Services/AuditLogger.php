<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Models\EmailAuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log an email related action.
     */
    public function log(
        EmailAccount $account,
        ?int $userId,
        string $action,
        array $metadata = [],
        ?string $entityType = null,
        ?int $entityId = null
    ): EmailAuditLog {
        return EmailAuditLog::create([
            'email_account_id' => $account->id,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Helper to log sync activity.
     */
    public function logSync(EmailAccount $account, string $status, array $details = []): EmailAuditLog
    {
        return $this->log($account, null, 'sync_' . $status, $details, 'EmailAccount', $account->id);
    }
}
