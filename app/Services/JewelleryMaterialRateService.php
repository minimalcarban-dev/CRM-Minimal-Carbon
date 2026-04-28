<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JewelleryMaterialRateService
{
    private const GOLD_MARKUP_PERCENT = 20;
    private const SILVER_925_PURITY = 0.925;
    private const SILVER_935_PURITY = 0.935;
    private const PLATINUM_950_PURITY = 0.95;

    public function __construct(
        protected GoldRateService $goldRateService
    ) {}

    public function currentRates(): array
    {
        $usdRate = $this->usdRate();
        $gold = $this->goldRateService->getRateForDate(now()->toDateString());
        $rawInrPerGram = (float) ($gold['rate_inr_per_gram'] ?? 0);
        $adjustedInrPerGram = $rawInrPerGram * (1 + (self::GOLD_MARKUP_PERCENT / 100));
        $adjustedUsdPerGram = $adjustedInrPerGram * $usdRate;
        $silverInrPerGram = $this->silverInrPerGram();
        $silverUsdPerGram = $silverInrPerGram * $usdRate;
        $platinum950UsdPerGram = $this->getPlatinumRate();

        return [
            'success' => $rawInrPerGram > 0,
            'gold_markup_percent' => self::GOLD_MARKUP_PERCENT,
            'gold_raw_inr_per_gram' => round($rawInrPerGram, 2),
            'gold_adjusted_inr_per_gram' => round($adjustedInrPerGram, 2),
            'gold_adjusted_usd_per_gram' => round($adjustedUsdPerGram, 4),
            'usd_rate' => round($usdRate, 6),
            'silver_inr_per_gram' => round($silverInrPerGram, 2),
            'silver_base_usd_per_gram' => round($silverUsdPerGram, 4),
            'silver_925_usd_per_gram' => round($silverUsdPerGram * self::SILVER_925_PURITY, 4),
            'silver_935_usd_per_gram' => round($silverUsdPerGram * self::SILVER_935_PURITY, 4),
            'platinum_950_usd_per_gram' => round($platinum950UsdPerGram, 4),
            'source' => $gold['source'] ?? 'unavailable',
            'fetched_at' => now(),
            'message' => $gold['message'] ?? null,
        ];
    }

    protected function usdRate(): float
    {
        return Cache::remember('currency_usd_inr_jewellery_pricing', 3600, function () {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->get('https://currency-rate-exchange-api.onrender.com/inr');

                if ($response->successful()) {
                    $rates = $response->json('rates');
                    if (isset($rates['inr']['usd'])) {
                        return (float) $rates['inr']['usd'];
                    }
                    if (isset($rates['USD'])) {
                        return (float) $rates['USD'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Jewellery pricing currency API failed: ' . $e->getMessage());
            }

            return 0.0118;
        });
    }

    protected function settingFloat(string $key, float $default): float
    {
        return (float) AppSetting::get($key, (string) $default);
    }

    private function silverInrPerGram(): float
    {
        return Cache::remember('jewellery_pricing_silver_inr_per_gram', 1800, function () {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->get('https://custom-gold-api.onrender.com/silver');

                if ($response->successful()) {
                    // Primary source now exposes silver on dedicated /silver endpoint.
                    $silverPerGram = (float) ($response->json('prices.silver.per_gram') ?? 0);
                    if ($silverPerGram > 0) {
                        return $silverPerGram;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Jewellery pricing silver API failed: ' . $e->getMessage());
            }

            Log::warning('Jewellery pricing silver rate unavailable from custom-gold-api.onrender.com');

            return 0.0;
        });
    }

    private function getPlatinumRate(): float
    {
        // 1. Priority: config value (sourced from .env via config/diamond.php)
        $envRate = config('diamond.jewellery_platinum_rate');
        if ($envRate !== null) {
            return (float) $envRate;
        }

        // 2. Fallback: Database setting
        $manual950Rate = $this->settingFloat('jewellery_pricing.platinum_950_rate_usd_per_gram', 30);

        // Future-ready hook: swap this assignment with API-derived base * 0.95.
        return max(0, $manual950Rate * (self::PLATINUM_950_PURITY / 0.95));
    }
}
