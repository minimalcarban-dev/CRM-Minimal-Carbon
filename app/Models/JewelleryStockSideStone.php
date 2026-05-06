<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JewelleryStockSideStone extends Model
{
    protected $fillable = [
        'jewellery_stock_id',
        'stone_type_id',
        'weight',
        'count',
        'stone_shape_id',
        'stone_color_id',
        'stone_clarity_id',
        'stone_cut_id',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(JewelleryStock::class, 'jewellery_stock_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(StoneType::class, 'stone_type_id');
    }

    public function shape(): BelongsTo
    {
        return $this->belongsTo(StoneShape::class, 'stone_shape_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(StoneColor::class, 'stone_color_id');
    }

    public function clarity(): BelongsTo
    {
        return $this->belongsTo(DiamondClarity::class, 'stone_clarity_id');
    }

    public function cut(): BelongsTo
    {
        return $this->belongsTo(DiamondCut::class, 'stone_cut_id');
    }
}
