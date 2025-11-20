<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Persist an audit log entry.
     */
    public static function log(string $event, Model $auditable, ?int $userId = null, array $oldValues = [], array $newValues = []): void
    {
        try {
            AuditLog::create([
                'auditable_type' => get_class($auditable),
                'auditable_id' => $auditable->getKey(),
                'user_id' => $userId,
                'event' => $event,
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently ignore logging failures to avoid breaking core flow
        }
    }
}
