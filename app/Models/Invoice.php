<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'invoice_date',
        'company_id',
        'invoice_type',
        'place_of_supply',
        'payment_terms',
        'billed_to_id',
        'shipped_to_id',
        'taxable_amount',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
        'total_invoice_value',
        'status',
        'copy_type'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function billedTo()
    {
        return $this->belongsTo(Party::class, 'billed_to_id');
    }

    public function shippedTo()
    {
        return $this->belongsTo(Party::class, 'shipped_to_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
