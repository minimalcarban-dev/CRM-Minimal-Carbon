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

        // Standard sizes from 0.8 to 4.0 mm
        $sizes = [];
        for ($i = 8; $i <= 40; $i++) {
            $val = $i / 10;
            $sizes[] = number_format($val, 1) . ' mm';
        }
        // Add some sieve sizes or specific ones if needed, but simple mm sizes are good start.

        foreach ($categories as $category) {
            if (empty($category->allowed_shapes))
                continue;

            foreach ($category->allowed_shapes as $shape) {
                foreach ($sizes as $size) {
                    MeleeDiamond::updateOrCreate(
                        [
                            'melee_category_id' => $category->id,
                            'shape' => $shape,
                            'size_label' => $size,
                        ],
                        [
                            // Defaults for new records
                            'color' => 'White', // Default color, user can edit? or maybe null
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
