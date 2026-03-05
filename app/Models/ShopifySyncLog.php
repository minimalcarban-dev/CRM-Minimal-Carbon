<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifySyncLog extends Model
{
    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'status',
        'request_payload',
        'response_message',
    ];

    protected $casts = [
        'request_payload' => 'array',
    ];

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'Success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'Failed');
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     */

    /**
     * Convenience method to log a sync action.
     */
    public static function logAction(
        string $action,
        string $entityType,
        ?string $entityId,
        string $status,
        ?string $responseMessage = null,
        ?array $requestPayload = null,
        ): self
    {
        return static::create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => $status,
            'response_message' => $responseMessage,
            'request_payload' => $requestPayload,
        ]);
    }
}
