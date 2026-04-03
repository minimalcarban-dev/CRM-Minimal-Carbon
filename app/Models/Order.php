<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AuditLog;
use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tax ID type options with display labels
     */
    public const TAX_ID_TYPES = [
        'tax_id' => 'TAX ID',
        'vat_id' => 'VAT ID',
        'ioss_no' => 'IOSS NO',
        'uid_vat_no' => 'UID VAT NO',
        'other' => 'OTHER',
    ];

    protected $fillable = [
        'order_type',
        'client_id',
        'client_name',
        'client_address',
        'client_mobile',
        'client_tax_id',
        'client_tax_id_type',
        'client_email',
        'jewellery_details',
        'diamond_details',
        'diamond_sku',
        'diamond_skus', // New: supports multiple diamond SKUs
        'diamond_prices', // Individual prices for each diamond SKU
        'product_other',
        'images',
        'order_pdfs',
        'gold_detail_id',
        'ring_size_id',
        'setting_type_id',
        'earring_type_id',
        'company_id',
        'factory_id',
        'diamond_status',
        'gross_sell',
        'payment_status',
        'amount_received',
        'amount_due',
        'note',
        'shipping_company_name',
        'tracking_number',
        'tracking_url',
        'tracking_status',
        'tracking_history',
        'last_tracker_sync',
        'dispatch_date',
        'submitted_by',
        'last_modified_by',
        'special_notes',
        'cancel_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'images' => 'array',
        'order_pdfs' => 'array',
        'diamond_skus' => 'array', // New: cast to array for multi-SKU support
        'diamond_prices' => 'array', // Cast JSON to array for diamond prices
        'melee_entries' => 'array', // Multi-melee entries JSON
        'tracking_history' => 'array',
        'last_tracker_sync' => 'datetime',
        'dispatch_date' => 'date',
        'cancelled_at' => 'datetime',
        'gross_sell' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'amount_due' => 'decimal:2',
    ];

    /**
     * Relations
     */

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function factoryRelation(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }

    
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'submitted_by');
    }

    public function goldDetail()
    {
        return $this->belongsTo(MetalType::class, 'gold_detail_id');
    }

    public function ringSize()
    {
        return $this->belongsTo(RingSize::class, 'ring_size_id');
    }

    public function settingType()
    {
        return $this->belongsTo(SettingType::class, 'setting_type_id');
    }

    public function earringDetail()
    {
        return $this->belongsTo(ClosureType::class, 'earring_type_id');
    }

    public function lastModifier()
    {
        return $this->belongsTo(Admin::class, 'last_modified_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(Admin::class, 'cancelled_by');
    }

    public function meleeDiamond()
    {
        return $this->belongsTo(MeleeDiamond::class, 'melee_diamond_id');
    }

    /**
     * All audit log entries for this order (edit history), newest first.
     */
    public function editHistory()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderByDesc('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class)->orderByDesc('received_at')->orderByDesc('id');
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        $summary = $this->resolvePaymentSummary(null, null, null, false);

        return [
            'full' => 'Full Paid',
            'partial' => 'Partial Paid',
            'due' => 'Due',
        ][$summary['payment_status'] ?? 'due'] ?? strtoupper((string) ($summary['payment_status'] ?? 'N/A'));
    }

    public function getAmountReceivedTotalAttribute(): float
    {
        return $this->resolvePaymentSummary(null, null, null, false)['amount_received'];
    }

    public function getAmountDueTotalAttribute(): float
    {
        return $this->resolvePaymentSummary(null, null, null, false)['amount_due'];
    }

    public function getRemainingBalanceAttribute(): float
    {
        return $this->amount_due_total;
    }

    public function isFullyPaid(): bool
    {
        return $this->resolvePaymentSummary(null, null, null, false)['payment_status'] === 'full';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->resolvePaymentSummary(null, null, null, false)['payment_status'] === 'partial';
    }

    public function isDue(): bool
    {
        return $this->resolvePaymentSummary(null, null, null, false)['payment_status'] === 'due';
    }

    public function getPaymentSummaryAttribute(): array
    {
        return $this->resolvePaymentSummary(null, null, null, false);
    }

    public function syncPaymentSummary(?float $amountReceived = null, ?string $status = null, ?float $amountDue = null): array
    {
        $summary = $this->resolvePaymentSummary($amountReceived, $status, $amountDue, false);

        $this->forceFill([
            'payment_status' => $summary['payment_status'],
            'amount_received' => $summary['amount_received'],
            'amount_due' => $summary['amount_due'],
        ])->saveQuietly();

        return $summary;
    }

    public function refreshPaymentSummaryFromPayments(): array
    {
        $summary = $this->resolvePaymentSummary(null, null, null, true);

        $this->forceFill([
            'payment_status' => $summary['payment_status'],
            'amount_received' => $summary['amount_received'],
            'amount_due' => $summary['amount_due'],
        ])->saveQuietly();

        return $summary;
    }

    private function resolvePaymentSummary(?float $overrideAmountReceived = null, ?string $overrideStatus = null, ?float $overrideAmountDue = null, bool $usePayments = true): array
    {
        $grossSell = round((float) ($this->gross_sell ?? 0), 2);
        $paymentStatus = $overrideStatus ?? ($this->payment_status ?: null);
        $ledgerAmount = $usePayments ? round((float) $this->payments()->sum('amount'), 2) : null;
        $hasLedgerPayments = $usePayments ? $this->payments()->exists() : false;

        if ($hasLedgerPayments) {
            $amountReceived = min($grossSell, max(0, $ledgerAmount ?? 0));
            $amountDue = round(max($grossSell - $amountReceived, 0), 2);
            $paymentStatus = $amountReceived <= 0
                ? 'due'
                : ($amountDue > 0 ? 'partial' : 'full');

            return [
                'payment_status' => $paymentStatus,
                'amount_received' => $amountReceived,
                'amount_due' => $amountDue,
            ];
        }

        $storedAmountReceived = $overrideAmountReceived ?? ($this->amount_received !== null ? (float) $this->amount_received : null);
        $storedAmountDue = $overrideAmountDue ?? ($this->amount_due !== null ? (float) $this->amount_due : null);

        if ($paymentStatus === null && $storedAmountReceived === null && $storedAmountDue === null && $grossSell > 0) {
            $paymentStatus = 'full';
        }

        if ($paymentStatus === 'due') {
            $amountReceived = 0.0;
            $amountDue = $storedAmountDue !== null ? round(max(0, $storedAmountDue), 2) : $grossSell;
        } elseif ($paymentStatus === 'partial') {
            $amountReceived = round(max(0, (float) ($storedAmountReceived ?? 0)), 2);
            if ($storedAmountDue !== null) {
                $amountDue = round(max(0, $storedAmountDue), 2);
            } else {
                $amountDue = round(max($grossSell - $amountReceived, 0), 2);
            }
        } elseif ($paymentStatus === 'full') {
            $amountReceived = $storedAmountReceived !== null ? round(max(0, $storedAmountReceived), 2) : $grossSell;
            $amountDue = 0.0;
        } else {
            if ($storedAmountReceived === null && $storedAmountDue === null) {
                $amountReceived = $grossSell;
                $amountDue = 0.0;
                $paymentStatus = $grossSell > 0 ? 'full' : 'due';
            } else {
                $amountReceived = round(max(0, (float) ($storedAmountReceived ?? 0)), 2);
                $amountDue = round(max(0, (float) ($storedAmountDue ?? max($grossSell - $amountReceived, 0))), 2);
                $paymentStatus = $amountDue > 0
                    ? ($amountReceived > 0 ? 'partial' : 'due')
                    : 'full';
            }
        }

        if ($grossSell <= 0) {
            $paymentStatus = 'full';
            $amountReceived = 0.0;
            $amountDue = 0.0;
        }

        if ($amountReceived > $grossSell && $grossSell > 0) {
            $amountReceived = $grossSell;
        }

        $amountDue = round(max($grossSell - $amountReceived, 0), 2);
        if ($paymentStatus === 'due' && $amountReceived <= 0 && $grossSell > 0) {
            $amountDue = $storedAmountDue !== null ? round(max(0, $storedAmountDue), 2) : $grossSell;
        }

        return [
            'payment_status' => $paymentStatus,
            'amount_received' => round($amountReceived, 2),
            'amount_due' => round($amountDue, 2),
        ];
    }

    /**
     * Compatibility helpers to extract structured client info from legacy `client_details` text.
     * This attempts JSON decode first, then falls back to simple regex/line parsing.
     */
    public function parseLegacyClient(): array
    {
        $raw = $this->client_details ?? '';
        if (!$raw || !is_string($raw))
            return [];

        // Try JSON first
        $decoded = @json_decode($raw, true);
        if (is_array($decoded)) {
            return [
                'name' => $decoded['client_name'] ?? $decoded['name'] ?? null,
                'email' => $decoded['client_email'] ?? $decoded['email'] ?? null,
                'mobile' => $decoded['client_mobile'] ?? $decoded['mobile'] ?? null,
                'address' => $decoded['client_address'] ?? $decoded['address'] ?? null,
                'tax_id' => $decoded['client_tax_id'] ?? $decoded['tax_id'] ?? null,
            ];
        }

        // Plain text fallback: attempt to extract email, phone, tax id, and name (first non-empty line)
        $result = [
            'name' => null,
            'email' => null,
            'mobile' => null,
            'address' => null,
            'tax_id' => null,
        ];

        // Email
        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $raw, $m)) {
            $result['email'] = $m[0];
        }

        // Phone numbers (simple patterns), capture international and local formats
        if (preg_match('/(\+?\d[\d\s\-()]{6,}\d)/', $raw, $m2)) {
            $result['mobile'] = trim($m2[0]);
        }

        // Tax-like ids (GST / VAT) - look for alphanumeric groups
        if (preg_match('/(GSTIN|GST|VAT|TIN)[:\s]*([A-Z0-9-]{5,})/i', $raw, $m3)) {
            $result['tax_id'] = $m3[2] ?? $m3[1] ?? null;
        }

        // Lines: first non-empty line as name, and the rest as address (excluding detected email/phone lines)
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $cleanLines = array_filter(array_map('trim', $lines));
        if ($cleanLines) {
            $first = array_shift($cleanLines);
            // If first line contains email or phone, skip it
            if ($result['email'] && strpos($first, $result['email']) !== false) {
                $first = $cleanLines ? array_shift($cleanLines) : null;
            }
            $result['name'] = $first ?: null;
            // Remaining lines joined are address
            $addr = implode(', ', $cleanLines);
            // Remove email/phone occurrences from address
            if ($result['email'])
                $addr = str_replace($result['email'], '', $addr);
            if ($result['mobile'])
                $addr = str_replace($result['mobile'], '', $addr);
            $result['address'] = trim(preg_replace('/\s{2,}/', ' ', $addr));
        }

        return $result;
    }

    public function getDisplayClientNameAttribute()
    {
        return $this->client_name ?: ($this->parseLegacyClient()['name'] ?? null);
    }

    public function getDisplayClientEmailAttribute()
    {
        return $this->client_email ?: ($this->parseLegacyClient()['email'] ?? null);
    }

    public function getDisplayClientMobileAttribute()
    {
        return $this->client_mobile ?: ($this->parseLegacyClient()['mobile'] ?? null);
    }

    public function getDisplayClientAddressAttribute()
    {
        return $this->client_address ?: ($this->parseLegacyClient()['address'] ?? null);
    }

    public function getDisplayClientTaxIdAttribute()
    {
        return $this->client_tax_id ?: ($this->parseLegacyClient()['tax_id'] ?? null);
    }
}
