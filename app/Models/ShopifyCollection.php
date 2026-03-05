<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyCollection extends Model
{
    protected $fillable = [
        'shopify_collection_id',
        'title',
        'handle',
    ];
}
