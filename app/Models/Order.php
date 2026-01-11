<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_type',
        'client_name',
        'client_address',
        'client_mobile',
        'client_tax_id',
        'client_email',
        'jewellery_details',
        'diamond_details',
        'diamond_sku',
        'product_other',
        'images',
        'order_pdfs',
        'gold_detail_id',
        'ring_size_id',
        'setting_type_id',
        'earring_type_id',
        'company_id',
        'diamond_status',
        'gross_sell',
        'note',
        'shipping_company_name',
        'tracking_number',
        'tracking_url',
        'dispatch_date',
        'submitted_by',
        'last_modified_by',
        'special_notes',
    ];

    protected $casts = [
        'images' => 'array',
        'order_pdfs' => 'array',
        'dispatch_date' => 'date',
        'gross_sell' => 'decimal:2',
    ];

    /**
     * Relations
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
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
