<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'address',
        'mobile',
        'tax_id',
        'created_by',
    ];

    /**
     * Get the admin who created this client.
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get all orders for this client.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the total value of all orders for this client.
     */
    public function getTotalSpendAttribute()
    {
        return $this->orders()->sum('gross_sell');
    }
}
