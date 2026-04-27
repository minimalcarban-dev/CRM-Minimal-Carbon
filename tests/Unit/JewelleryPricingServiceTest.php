<?php

use App\Models\Admin;
use App\Services\JewelleryMaterialRateService;
use App\Services\JewelleryPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function jewelleryPricingRows(array $overrides = []): array
{
    return array_replace_recursive([
        'gold_10k__none' => [
            'color_weights' => ['yellow' => 10, 'white' => 0, 'rose' => 0],
            'stone_cost' => 50,
            'extra_cost' => 25,
            'labor_rate_usd_per_gram' => 20,
            'commission_percent' => 20,
            'profit_percent' => 25,
            'sales_markup_percent' => 10,
            'is_default_listing' => true,
        ],
    ], $overrides);
}

function jewelleryPricingService(): JewelleryPricingService
{
    $rates = Mockery::mock(JewelleryMaterialRateService::class);
    $rates->shouldReceive('currentRates')->andReturn([
        'success' => true,
        'gold_markup_percent' => 20,
        'gold_raw_inr_per_gram' => 1000,
        'gold_adjusted_inr_per_gram' => 1200,
        'gold_adjusted_usd_per_gram' => 100,
        'usd_rate' => 0.083333,
        'silver_base_usd_per_gram' => 2,
        'silver_925_usd_per_gram' => 1.85,
        'silver_935_usd_per_gram' => 1.87,
        'platinum_950_usd_per_gram' => 30,
        'source' => 'test',
        'fetched_at' => now(),
    ]);

    return new JewelleryPricingService($rates);
}

it('calculates gold pricing with purity labor commission profit and markup', function () {
    $admin = new Admin();
    $admin->is_super = true;

    $rows = jewelleryPricingService()->calculateMatrix(jewelleryPricingRows(), $admin);
    $default = collect($rows)->firstWhere('is_default_listing', true);

    expect($default['purity_percent'])->toBe(41.7)
        ->and($default['base_rate_usd_per_gram'])->toBe(41.7)
        ->and($default['color_weights'])->toMatchArray(['yellow' => 10.0, 'white' => 0.0, 'rose' => 0.0])
        ->and($default['material_value'])->toBe(417.0)
        ->and($default['labor_cost'])->toBe(200.0)
        ->and($default['subtotal_cost'])->toBe(692.0)
        ->and($default['commission_amount'])->toBe(138.4)
        ->and($default['profit_amount'])->toBe(207.6)
        ->and($default['sales_markup_amount'])->toBe(103.8)
        ->and($default['listing_price'])->toBe(1141.8);
});

it('ignores restricted posted assumptions for regular admins', function () {
    $admin = new Admin();
    $admin->is_super = false;

    $rows = jewelleryPricingService()->calculateMatrix(jewelleryPricingRows([
        'gold_10k__none' => [
            'labor_rate_usd_per_gram' => 999,
            'commission_percent' => 999,
            'profit_percent' => 999,
            'sales_markup_percent' => 999,
        ],
    ]), $admin);
    $default = collect($rows)->firstWhere('is_default_listing', true);

    expect($default['labor_rate_usd_per_gram'])->toBe(20.0)
        ->and($default['commission_percent'])->toBe(20.0)
        ->and($default['profit_percent'])->toBe(25.0)
        ->and($default['sales_markup_percent'])->toBe(0.0);
});

it('uses heaviest gold color as default listing weight', function () {
    $admin = new Admin();
    $admin->is_super = true;

    $rows = jewelleryPricingService()->calculateMatrix([
        'gold_10k__none' => [
            'color_weights' => ['yellow' => 4.2, 'white' => 5.1, 'rose' => 3.4],
            'stone_cost' => 0,
            'extra_cost' => 0,
            'is_default_listing' => true,
        ],
    ], $admin);
    $default = collect($rows)->firstWhere('is_default_listing', true);

    expect($default['net_weight_grams'])->toBe(5.1)
        ->and($default['listing_price'])->toBeGreaterThan(0);
});
