<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JewelleryStockSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $types = ['ring', 'earrings', 'tennis_bracelet', 'other'];
        $statuses = ['in_stock', 'low_stock', 'out_of_stock'];

        // Ensure there are some MetalTypes and RingSizes
        if (MetalType::count() === 0) {
            MetalType::create(['name' => '14K Gold', 'is_active' => true]);
            MetalType::create(['name' => 'Platinum', 'is_active' => true]);
        }
        if (RingSize::count() === 0) {
            RingSize::create(['name' => 'US 6', 'is_active' => true]);
            RingSize::create(['name' => 'US 7', 'is_active' => true]);
        }

        $metalTypes = MetalType::pluck('id')->toArray();
        $ringSizes = RingSize::pluck('id')->toArray();

        for ($i = 0; $i < 100; $i++) {
            $type = $faker->randomElement($types);
            JewelleryStock::create([
                'sku' => strtoupper($faker->unique()->bothify('JS-####-???')),
                'type' => $type,
                'name' => $faker->words(3, true),
                'metal_type_id' => $faker->randomElement($metalTypes),
                'ring_size_id' => ($type === 'ring') ? $faker->randomElement($ringSizes) : null,
                'weight' => $faker->randomFloat(3, 1, 50),
                'quantity' => $faker->numberBetween(1, 100),
                'low_stock_threshold' => 5,
                'purchase_price' => 0, // Set to 0 since user asked not to add a purchase price, but it might be NOT NULL
                'selling_price' => $faker->randomFloat(2, 500, 10000),
                'status' => 'in_stock', // boot method will override if needed
                'description' => $faker->sentence(10),
                'image_url' => null,
            ]);
        }
    }
}
