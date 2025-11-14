<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Diamond extends Model
{
    use HasFactory;

    protected $fillable = [
        'stockid',
        'sku',
        'price',
        'listing_price',
        'cut',
        'shape',
        'measurement',
        'number_of_pics',
        'barcode_number',
        'barcode_image_url',
        'description',
        'admin_id',
        'note',
        'diamond_type',
        'multi_img_upload',
        'assign_by',
        'assigned_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'listing_price' => 'decimal:2',
        'multi_img_upload' => 'array',
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the admin who is assigned this diamond
     */
    public function assignedAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get the admin who assigned this diamond
     */
    public function assignedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'assign_by');
    }

    /**
     * Get all admins assigned to this diamond (many-to-many)
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'diamond_admin', 'diamond_id', 'admin_id')
            ->withPivot('assign_by', 'assigned_at')
            ->withTimestamps();
    }
}
