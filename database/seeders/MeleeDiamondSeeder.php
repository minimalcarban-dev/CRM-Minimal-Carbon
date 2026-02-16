<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;

class MeleeDiamondSeeder extends Seeder
{
    public function run()
    {
        $categories = MeleeCategory::all();

        // Default sizes (numeric values only - will be stored as "shape-size" format)
        $defaultSizes = [0.8, 0.9, 1.0, 1.1, 1.2, 1.3, 1.5, 1.7, 2.0, 2.5, 3.0];

        foreach ($categories as $category) {
            if (empty($category->allowed_shapes))
                continue;

            foreach ($category->allowed_shapes as $shape) {
                foreach ($defaultSizes as $size) {
                    // size_label stored as "shape-size" in lowercase
                    // e.g., "round-1.0", "pear-2.5"
                    $sizeLabel = strtolower($shape) . '-' . $size;

                    MeleeDiamond::updateOrCreate(
                        [
                            'melee_category_id' => $category->id,
                            'shape' => $shape,
                            'size_label' => $sizeLabel,
                        ],
                        [
                            'color' => null,
                            'sieve_size' => null,
                            'total_pieces' => 0,
                            'available_pieces' => 0,
                            'sold_pieces' => 0,
                            'total_carat_weight' => 0,
                            'available_carat_weight' => 0,
                            'purchase_price_per_ct' => 0,
                            'listing_price_per_ct' => 0,
                            'status' => 'out_of_stock',
                            'low_stock_threshold' => 10,
                        ]
                    );
                }
            }
        }
    }
}
