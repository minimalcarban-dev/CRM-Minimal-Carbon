<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','address','gst_no','pan_no','state','state_code','country','tax_id','is_foreign','email','phone'
    ];

    public function billedInvoices()
    {
        return $this->hasMany(Invoice::class, 'billed_to_id');
    }

    public function shippedInvoices()
    {
        return $this->hasMany(Invoice::class, 'shipped_to_id');
    }
}
