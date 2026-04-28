<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AppSetting;
use App\Models\JewelleryStock;
use Illuminate\Support\Collection;

class JewelleryPricingService
{
    public const MATERIALS = [
        'silver_925' => ['label' => '925 Silver', 'purity' => 92.5, 'colors' => [null]],
        'silver_935' => ['label' => '935 Argentium', 'purity' => 93.5, 'colors' => [null]],
        'platinum_950' => ['label' => '950 Platinum', 'purity' => 95.0, 'colors' => [null]],
        'gold_10k' => ['label' => '10K Gold', 'purity' => 41.7, 'colors' => ['yellow', 'white', 'rose']],
        'gold_14k' => ['label' => '14K Gold', 'purity' => 58.5, 'colors' => ['yellow', 'white', 'rose']],
        'gold_18k' => ['label' => '18K Gold', 'purity' => 75.0, 'colors' => ['yellow', 'white', 'rose']],
        'gold_22k' => ['label' => '22K Gold', 'purity' => 91.7, 'colors' => ['yellow', 'white', 'rose']],
    ];

    public function __construct(
        protected JewelleryMaterialRateService $rateService
    ) {
    }

    public function defaultsFor(?Admin $admin): array
    {
        return [
            'labor_rate_usd_per_gram' => $this->settingFloat('jewellery_pricing.labor_rate_usd_per_gram', 20),
            'commission_percent' => $this->settingFloat('jewellery_pricing.default_commission_percent', 20),
            'profit_percent' => $this->settingFloat('jewellery_pricing.default_profit_percent', 25),
            'sales_markup_percent' => $this->settingFloat('jewellery_pricing.default_sales_markup_percent', 0),
            'platinum_950_rate_usd_per_gram' => config('diamond.jewellery_platinum_rate')
                ?? $this->settingFloat('jewellery_pricing.platinum_950_rate_usd_per_gram', 30),
            'can_edit_labor' => (bool) ($admin?->is_super),
            'can_edit_commission' => (bool) ($admin && ($admin->is_super || $admin->hasPermission('jewellery_stock.edit_commission'))),
            'can_edit_profit' => (bool) ($admin && ($admin->is_super || $admin->hasPermission('jewellery_stock.edit_profit'))),
            'can_edit_sales_markup' => (bool) ($admin && ($admin->is_super || $admin->hasPermission('jewellery_stock.edit_sales_markup'))),
            'can_view_profit' => (bool) ($admin && ($admin->is_super || $admin->hasPermission('jewellery_stock.view_profit'))),
        ];
    }

    public function blankVariantMatrix(): array
    {
        $rows = [];
        foreach (self::MATERIALS as $materialCode => $material) {
            $key = $this->variantKey($materialCode, null);
            $rows[$key] = [
                'key' => $key,
                'material_code' => $materialCode,
                'material_label' => $material['label'],
                'metal_color' => null,
                'variant_label' => $material['label'],
                'net_weight_grams' => 0,
                'color_weights' => str_starts_with($materialCode, 'gold_')
                    ? ['yellow' => 0, 'white' => 0, 'rose' => 0]
                    : null,
                'stone_cost' => 0,
                'extra_cost' => 0,
                'is_default_listing' => $materialCode === 'silver_925',
            ];
        }

        return $rows;
    }

    public function formRows(?Collection $savedRows = null): array
    {
        $rows = $this->blankVariantMatrix();

        foreach (($savedRows ?? collect()) as $row) {
            $key = $this->variantKey($row->material_code, null);
            if (!isset($rows[$key])) {
                continue;
            }

            $resolvedWeight = (float) $row->net_weight_grams;
            if (str_starts_with($row->material_code, 'gold_')) {
                $weights = is_array($row->color_weights) ? $row->color_weights : [];
                $resolvedWeight = max(
                    $resolvedWeight,
                    (float) ($weights['yellow'] ?? 0),
                    (float) ($weights['white'] ?? 0),
                    (float) ($weights['rose'] ?? 0),
                );
            }

            $rows[$key] = array_merge($rows[$key], [
                'net_weight_grams' => $resolvedWeight,
                'color_weights' => is_array($row->color_weights) ? $row->color_weights : null,
                'stone_cost' => (float) $row->stone_cost,
                'extra_cost' => (float) $row->extra_cost,
                'commission_percent' => (float) $row->commission_percent,
                'profit_percent' => (float) $row->profit_percent,
                'sales_markup_percent' => (float) $row->sales_markup_percent,
                'labor_rate_usd_per_gram' => (float) $row->labor_rate_usd_per_gram,
                'is_default_listing' => (bool) $row->is_default_listing,
            ]);
        }

        return $rows;
    }

    public function calculateMatrix(array $submittedRows, ?Admin $admin, ?array $rates = null): array
    {
        $rates ??= $this->rateService->currentRates();
        $defaults = $this->defaultsFor($admin);
        $rows = [];
        $defaultKey = $this->defaultKeyFromSubmittedRows($submittedRows) ?? 'silver_925__none';

        foreach ($this->blankVariantMatrix() as $key => $definition) {
            $input = $submittedRows[$key] ?? [];
            $materialCode = $definition['material_code'];
            $purity = self::MATERIALS[$materialCode]['purity'];
            $colorWeights = $this->normalizeColorWeights($materialCode, $input['color_weights'] ?? null);
            $weight = str_starts_with($materialCode, 'gold_')
                ? $this->defaultGoldWeight($colorWeights)
                : $this->positiveFloat($input['net_weight_grams'] ?? $definition['net_weight_grams']);
            $stoneCost = $this->positiveFloat($input['stone_cost'] ?? 0);
            $extraCost = $this->positiveFloat($input['extra_cost'] ?? 0);
            $laborRate = $defaults['can_edit_labor']
                ? $this->positiveFloat($input['labor_rate_usd_per_gram'] ?? $defaults['labor_rate_usd_per_gram'])
                : $defaults['labor_rate_usd_per_gram'];
            $commissionPercent = $defaults['can_edit_commission']
                ? $this->positiveFloat($input['commission_percent'] ?? $defaults['commission_percent'])
                : $defaults['commission_percent'];
            $profitPercent = $defaults['can_edit_profit']
                ? $this->positiveFloat($input['profit_percent'] ?? $defaults['profit_percent'])
                : $defaults['profit_percent'];
            $salesMarkupPercent = $defaults['can_edit_sales_markup']
                ? $this->positiveFloat($input['sales_markup_percent'] ?? $defaults['sales_markup_percent'])
                : $defaults['sales_markup_percent'];

            $baseRate = match (true) {
                str_starts_with($materialCode, 'silver_') => (float) ($rates['silver_base_usd_per_gram'] ?? 0) * ($purity / 100),
                $materialCode === 'platinum_950' => (float) ($rates['platinum_950_usd_per_gram'] ?? 0),
                str_starts_with($materialCode, 'gold_') => (float) ($rates['gold_adjusted_usd_per_gram'] ?? 0) * ($purity / 100),
                default => 0.0,
            };
            $materialValue = $weight * $baseRate;
            $laborCost = $weight * $laborRate;
            $subtotal = $materialValue + $laborCost + $stoneCost + $extraCost;
            $commissionAmount = $subtotal * ($commissionPercent / 100);
            $afterCommission = $subtotal + $commissionAmount;
            $profitAmount = $afterCommission * ($profitPercent / 100);
            $afterProfit = $afterCommission + $profitAmount;
            $salesMarkupAmount = $afterProfit * ($salesMarkupPercent / 100);
            $listingPrice = $afterProfit + $salesMarkupAmount;

            $rows[] = [
                'material_code' => $materialCode,
                'metal_color' => null,
                'net_weight_grams' => round($weight, 3),
                'color_weights' => $colorWeights,
                'purity_percent' => $purity,
                'base_rate_usd_per_gram' => round($baseRate, 4),
                'material_value' => round($materialValue, 2),
                'labor_rate_usd_per_gram' => round($laborRate, 2),
                'labor_cost' => round($laborCost, 2),
                'stone_cost' => round($stoneCost, 2),
                'extra_cost' => round($extraCost, 2),
                'subtotal_cost' => round($subtotal, 2),
                'commission_percent' => round($commissionPercent, 2),
                'commission_amount' => round($commissionAmount, 2),
                'profit_percent' => round($profitPercent, 2),
                'profit_amount' => round($profitAmount, 2),
                'sales_markup_percent' => round($salesMarkupPercent, 2),
                'sales_markup_amount' => round($salesMarkupAmount, 2),
                'listing_price' => round($listingPrice, 2),
                'rate_source' => $rates['source'] ?? null,
                'rate_fetched_at' => $rates['fetched_at'] ?? now(),
                'is_default_listing' => $key === $defaultKey,
            ];
        }

        if (!collect($rows)->contains('is_default_listing', true)) {
            $rows[0]['is_default_listing'] = true;
        }

        return $rows;
    }

    public function replacePricingRows(JewelleryStock $stock, array $submittedRows, ?Admin $admin): array
    {
        $rows = $this->calculateMatrix($submittedRows, $admin);
        $stock->pricingVariants()->delete();
        $stock->pricingVariants()->createMany($rows);

        $default = collect($rows)->firstWhere('is_default_listing', true) ?? $rows[0];
        $stock->forceFill([
            'purchase_price' => $default['subtotal_cost'],
            'selling_price' => $default['listing_price'],
        ])->save();

        return $rows;
    }

    public function variantKey(string $materialCode, ?string $color): string
    {
        return $materialCode . '__none';
    }

    protected function defaultKeyFromSubmittedRows(array $submittedRows): ?string
    {
        foreach ($submittedRows as $key => $row) {
            if (!empty($row['is_default_listing'])) {
                return (string) $key;
            }
        }

        return null;
    }

    protected function positiveFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return max(0.0, (float) $value);
    }

    protected function settingFloat(string $key, float $default): float
    {
        return (float) AppSetting::get($key, (string) $default);
    }

    protected function normalizeColorWeights(string $materialCode, mixed $weights): ?array
    {
        if (!str_starts_with($materialCode, 'gold_')) {
            return null;
        }

        $weights = is_array($weights) ? $weights : [];

        return [
            'yellow' => round($this->positiveFloat($weights['yellow'] ?? 0), 3),
            'white' => round($this->positiveFloat($weights['white'] ?? 0), 3),
            'rose' => round($this->positiveFloat($weights['rose'] ?? 0), 3),
        ];
    }

    protected function defaultGoldWeight(?array $colorWeights): float
    {
        $colorWeights = $colorWeights ?? [];

        return round(max(
            (float) ($colorWeights['yellow'] ?? 0),
            (float) ($colorWeights['white'] ?? 0),
            (float) ($colorWeights['rose'] ?? 0),
        ), 3);
    }
}
