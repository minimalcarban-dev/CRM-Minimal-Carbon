<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'logo',
        'gst_no',
        'state_code',
        'ein_cin_no',
        'address',
        'country',
        'bank_name',
        'account_no',
        'ifsc_code',
        'ad_code',
        'sort_code',
        'swift_code',
        'iban',
        'account_holder_name',
        'status'
    ];
    
    public $timestamps = true;
}
