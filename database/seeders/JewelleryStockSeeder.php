<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\JewelleryStock;
use App\Models\MetalType;
use App\Models\RingSize;
use App\Services\JewelleryPricingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JewelleryStockSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Ensure lookup tables exist (most come from other seeders, but keep safe defaults).
        if (MetalType::query()->count() === 0) {
            MetalType::query()->create(['name' => 'Gold', 'is_active' => true]);
            MetalType::query()->create(['name' => 'Platinum', 'is_active' => true]);
            MetalType::query()->create(['name' => 'Silver', 'is_active' => true]);
        }
        if (RingSize::query()->count() === 0) {
            RingSize::query()->create(['name' => 'US 6', 'is_active' => true]);
            RingSize::query()->create(['name' => 'US 7', 'is_active' => true]);
        }

        /** @var JewelleryPricingService $pricingService */
        $pricingService = app(JewelleryPricingService::class);
        $admin = Admin::query()->where('is_super', true)->first();

        $metalTypeGoldId = (int) (MetalType::query()->where('name', 'like', '%gold%')->value('id') ?? MetalType::query()->value('id'));
        $metalTypeSilverId = (int) (MetalType::query()->where('name', 'like', '%silver%')->value('id') ?? $metalTypeGoldId);
        $metalTypePlatinumId = (int) (MetalType::query()->where('name', 'like', '%platinum%')->value('id') ?? $metalTypeGoldId);
        $ringSizeId = (int) (RingSize::query()->value('id') ?? 1);

        $items = [
            [
                'sku' => 'SEED-RING-001',
                'type' => 'ring',
                'name' => 'Seed Classic Solitaire Ring',
                'metal_type_id' => $metalTypeGoldId,
                'metal_purity' => '18K',
                'ring_size_id' => $ringSizeId,
                'weight' => 4.250,
                'quantity' => 8,
                'low_stock_threshold' => 3,
                'description' => 'Sample seeded ring for jewellery pricing verification.',
                'default_variant' => 'gold_18k__none',
                'weights' => [
                    'gold_18k__none' => ['color_weights' => ['yellow' => 4.250, 'white' => 0, 'rose' => 0]],
                    'silver_925__none' => ['net_weight_grams' => 4.250],
                    'platinum_950__none' => ['net_weight_grams' => 4.250],
                ],
            ],
            [
                'sku' => 'SEED-EAR-001',
                'type' => 'earrings',
                'name' => 'Seed Stud Earrings Pair',
                'metal_type_id' => $metalTypeSilverId,
                'metal_purity' => '925',
                'ring_size_id' => null,
                'weight' => 3.600,
                'quantity' => 25,
                'low_stock_threshold' => 5,
                'description' => 'Sample seeded earrings for silver rate verification.',
                'default_variant' => 'silver_925__none',
                'weights' => [
                    'gold_18k__none' => ['color_weights' => ['yellow' => 0, 'white' => 3.600, 'rose' => 0]],
                    'silver_925__none' => ['net_weight_grams' => 3.600],
                    'platinum_950__none' => ['net_weight_grams' => 3.600],
                ],
            ],
            [
                'sku' => 'SEED-PEND-001',
                'type' => 'pendant',
                'name' => 'Seed Minimal Pendant',
                'metal_type_id' => $metalTypePlatinumId,
                'metal_purity' => '950 Plat',
                'ring_size_id' => null,
                'weight' => 5.125,
                'quantity' => 2,
                'low_stock_threshold' => 2,
                'description' => 'Sample seeded pendant for platinum rate verification.',
                'default_variant' => 'platinum_950__none',
                'weights' => [
                    'gold_18k__none' => ['color_weights' => ['yellow' => 5.125, 'white' => 0, 'rose' => 0]],
                    'silver_925__none' => ['net_weight_grams' => 5.125],
                    'platinum_950__none' => ['net_weight_grams' => 5.125],
                ],
            ],
        ];

        foreach ($items as $item) {
            $stock = JewelleryStock::query()->updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'metal_type_id' => $item['metal_type_id'],
                    'metal_purity' => $item['metal_purity'],
                    'ring_size_id' => $item['ring_size_id'],
                    'weight' => $item['weight'],
                    'quantity' => $item['quantity'],
                    'low_stock_threshold' => $item['low_stock_threshold'],
                    'purchase_price' => 0,
                    'selling_price' => 0,
                    'description' => $item['description'],
                    'image_url' => null,
                ]
            );

            // Build submitted rows for the pricing matrix.
            $submittedRows = [];
            foreach (array_keys(JewelleryPricingService::MATERIALS) as $materialCode) {
                $key = $pricingService->variantKey($materialCode, null);
                $submittedRows[$key] = [
                    'net_weight_grams' => 0,
                    'color_weights' => null,
                    'stone_cost' => 0,
                    'extra_cost' => 0,
                    'is_default_listing' => $key === $item['default_variant'],
                ];
            }

            // Apply deterministic weights.
            foreach ($item['weights'] as $key => $payload) {
                $submittedRows[$key] = array_merge($submittedRows[$key] ?? [], $payload);
            }

            // Persist computed pricing variants + update stock purchase/selling price.
            $pricingService->replacePricingRows($stock, $submittedRows, $admin);
        }
    }
}
