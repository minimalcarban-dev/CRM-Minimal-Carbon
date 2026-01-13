<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class OrderDraft extends Model
{
    use HasFactory;

    protected $table = 'order_drafts';

    protected $fillable = [
        'admin_id',
        'order_type',
        'form_data',
        'error_message',
        'source',
        'last_step',
        'client_name',
        'company_id',
        'expires_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Draft created within last N days
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Drafts that haven't expired yet
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Drafts expiring soon (within 7 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereBetween('expires_at', [
            Carbon::now(),
            Carbon::now()->addDays(7)
        ]);
    }

    /**
     * Filter by source
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Admin who created this draft
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Company (if selected in draft)
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Check if draft has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if draft is expiring soon (within 7 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isBetween(Carbon::now(), Carbon::now()->addDays(7));
    }

    /**
     * Calculate form completion percentage based on filled fields
     */
    public function getCompletionPercentageAttribute(): int
    {
        $formData = $this->form_data ?? [];
        if (empty($formData)) {
            return 0;
        }

        // Key fields to check for completion
        $keyFields = [
            'client_name',
            'client_email',
            'client_address',
            'company_id',
            'order_type',
            'diamond_details',
            'jewellery_details',
            'gross_sell',
            'dispatch_date'
        ];

        $filled = 0;
        foreach ($keyFields as $field) {
            if (!empty($formData[$field])) {
                $filled++;
            }
        }

        return (int) round(($filled / count($keyFields)) * 100);
    }

    /**
     * Get display label for source
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'auto_save' => 'Auto-saved',
            'error' => 'Error occurred',
            'manual' => 'Manually saved',
            default => 'Unknown'
        };
    }

    /**
     * Get order type label
     */
    public function getOrderTypeLabelAttribute(): string
    {
        return match ($this->order_type) {
            'ready_to_ship' => 'Ready to Ship',
            'custom_diamond' => 'Custom Diamond',
            'custom_jewellery' => 'Custom Jewellery',
            default => 'Unknown'
        };
    }

    /**
     * Set default expiry date on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($draft) {
            // Set expiry to 90 days from now if not set
            if (!$draft->expires_at) {
                $draft->expires_at = Carbon::now()->addDays(90);
            }

            // Extract client_name from form_data for quick display
            if (!$draft->client_name && !empty($draft->form_data['client_name'])) {
                $draft->client_name = $draft->form_data['client_name'];
            }

            // Extract company_id from form_data
            if (!$draft->company_id && !empty($draft->form_data['company_id'])) {
                $draft->company_id = $draft->form_data['company_id'];
            }
        });
    }
}
