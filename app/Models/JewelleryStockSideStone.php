<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JewelleryStockSideStone extends Model
{
    use HasFactory;

    protected $fillable = [
        'jewellery_stock_id',
        'stone_type_id',
        'stone_shape_id',
        'stone_color_id',
        'stone_clarity_id',
        'stone_cut_id',
        'carat_weight',
        'count',
        'total_weight',
    ];

    public function jewelleryStock(): BelongsTo
    {
        return $this->belongsTo(JewelleryStock::class);
    }

    public function stoneType(): BelongsTo
    {
        return $this->belongsTo(StoneType::class);
    }

    public function stoneShape(): BelongsTo
    {
        return $this->belongsTo(StoneShape::class);
    }

    public function stoneColor(): BelongsTo
    {
        return $this->belongsTo(StoneColor::class);
    }

    public function stoneClarity(): BelongsTo
    {
        return $this->belongsTo(DiamondClarity::class);
    }

    public function stoneCut(): BelongsTo
    {
        return $this->belongsTo(DiamondCut::class);
    }
}
