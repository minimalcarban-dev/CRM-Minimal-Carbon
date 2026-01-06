<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    // API Priority: Primary -> Backup 1 -> Backup 2
    private const PRIMARY_API_URL = 'https://open.er-api.com/v6/latest/USD';           // Open Exchange Rates (Google-sourced)
    private const BACKUP_API_1_URL = 'https://api.exchangerate-api.com/v4/latest/USD'; // ExchangeRate-API
    private const BACKUP_API_2_URL = 'https://currency-rate-exchange-api.onrender.com/usd'; // Render API

    private const CACHE_KEY = 'usd_inr_rate';
    private const BACKUP_CACHE_KEY = 'usd_inr_rate_backup';
    private const CACHE_DURATION = 3600; // 1 hour
    private const BACKUP_CACHE_DURATION = 2592000; // 30 days

    /**
     * Price fields that need INR to USD conversion
     */
    public const PRICE_FIELDS = [
        'per_ct',
        'purchase_price',
        'listing_price',
        'shipping_price',
        'duration_price',
        'sold_out_price',
        'profit',
        'actual_listing_price',
        'offer_calculation',
    ];

    /**
     * Get current INR to USD conversion rate with smart fallback
     * 
     * Priority:
     * 1. Cached rate (1 hour)
     * 2. Primary API (Open Exchange Rates - Google sourced)
     * 3. Backup API 1 (ExchangeRate-API)
     * 4. Backup API 2 (Render API)
     * 5. Backup cached rate (30 days)
     * 6. Throw exception - don't use wrong rate
     */
    public function getInrToUsdRate(): float
    {
        // 1. Try primary cache (1 hour)
        $rate = Cache::get(self::CACHE_KEY);
        if ($rate) {
            return $rate;
        }

        // 2. Try fetching from primary API (Open Exchange Rates)
        try {
            $rate = $this->fetchFromPrimaryApi();
            $this->cacheRate($rate);
            Log::info('Currency rate fetched from Primary API (Open Exchange Rates)', ['rate' => $rate]);
            return $rate;
        } catch (\Exception $e) {
            Log::warning('Primary currency API (Open Exchange Rates) failed', ['error' => $e->getMessage()]);
        }

        // 3. Try backup API 1 (ExchangeRate-API)
        try {
            $rate = $this->fetchFromBackupApi1();
            $this->cacheRate($rate);
            Log::info('Currency rate fetched from Backup API 1 (ExchangeRate-API)', ['rate' => $rate]);
            return $rate;
        } catch (\Exception $e) {
            Log::warning('Backup API 1 (ExchangeRate-API) failed', ['error' => $e->getMessage()]);
        }

        // 4. Try backup API 2 (Render API)
        try {
            $rate = $this->fetchFromBackupApi2();
            $this->cacheRate($rate);
            Log::info('Currency rate fetched from Backup API 2 (Render API)', ['rate' => $rate]);
            return $rate;
        } catch (\Exception $e) {
            Log::warning('Backup API 2 (Render API) also failed', ['error' => $e->getMessage()]);
        }

        // 5. Use last successful rate (stored for 30 days)
        $backupRate = Cache::get(self::BACKUP_CACHE_KEY);
        if ($backupRate) {
            Log::warning('Using cached backup rate (all APIs failed)', ['rate' => $backupRate]);
            // Re-cache for 1 hour to avoid repeated warnings
            Cache::put(self::CACHE_KEY, $backupRate, self::CACHE_DURATION);
            return $backupRate;
        }

        // 6. LAST RESORT: Throw error - admin must wait
        Log::error('All currency API sources failed and no backup rate available');
        throw new \Exception('Currency API unavailable. Please try again later.');
    }

    /**
     * Fetch rate from Primary API (Open Exchange Rates - Google sourced)
     * Response format: {"rates": {"INR": 83.50}}
     */
    private function fetchFromPrimaryApi(): float
    {
        $response = Http::timeout(10)->get(self::PRIMARY_API_URL);

        if (!$response->successful()) {
            throw new \Exception('Primary API returned status: ' . $response->status());
        }

        $data = $response->json();

        if (!isset($data['rates']['INR'])) {
            throw new \Exception('Invalid response structure from Primary API');
        }

        $inrRate = $data['rates']['INR']; // 1 USD = X INR
        return 1 / $inrRate; // Convert to: 1 INR = X USD
    }

    /**
     * Fetch rate from Backup API 1 (ExchangeRate-API)
     * Response format: {"rates": {"INR": 83.50}}
     */
    private function fetchFromBackupApi1(): float
    {
        $response = Http::timeout(10)->get(self::BACKUP_API_1_URL);

        if (!$response->successful()) {
            throw new \Exception('Backup API 1 returned status: ' . $response->status());
        }

        $data = $response->json();

        if (!isset($data['rates']['INR'])) {
            throw new \Exception('Invalid response structure from Backup API 1');
        }

        $inrRate = $data['rates']['INR']; // 1 USD = X INR
        return 1 / $inrRate; // Convert to: 1 INR = X USD
    }

    /**
     * Fetch rate from Backup API 2 (Render API)
     * Response format: {"rates": {"usd": {"inr": 83.50}}}
     */
    private function fetchFromBackupApi2(): float
    {
        $response = Http::timeout(10)->get(self::BACKUP_API_2_URL);

        if (!$response->successful()) {
            throw new \Exception('Backup API 2 returned status: ' . $response->status());
        }

        $data = $response->json();

        if (!isset($data['rates']['usd']['inr'])) {
            throw new \Exception('Invalid response structure from Backup API 2');
        }

        $inrRate = $data['rates']['usd']['inr']; // 1 USD = X INR
        return 1 / $inrRate; // Convert to: 1 INR = X USD
    }

    /**
     * Cache the rate in both primary and backup caches
     */
    private function cacheRate(float $rate): void
    {
        Cache::put(self::CACHE_KEY, $rate, self::CACHE_DURATION);
        Cache::put(self::BACKUP_CACHE_KEY, $rate, self::BACKUP_CACHE_DURATION);

        Log::info('Currency rate cached', [
            'rate' => $rate,
            'inr_per_usd' => round(1 / $rate, 2)
        ]);
    }

    /**
     * Convert INR amount to USD
     */
    public function inrToUsd(?float $inrAmount): ?float
    {
        if ($inrAmount === null || $inrAmount === 0) {
            return $inrAmount;
        }

        $rate = $this->getInrToUsdRate();
        return round($inrAmount * $rate, 2);
    }

    /**
     * Convert multiple price fields from INR to USD
     */
    public function convertPriceFields(array $data, ?array $fields = null): array
    {
        $fields = $fields ?? self::PRICE_FIELDS;
        $rate = $this->getInrToUsdRate();

        foreach ($fields as $field) {
            if (isset($data[$field]) && is_numeric($data[$field]) && $data[$field] > 0) {
                $data[$field] = round($data[$field] * $rate, 2);
            }
        }

        return $data;
    }

    /**
     * Get current rate info (for display purposes)
     */
    public function getRateInfo(): array
    {
        try {
            $rate = $this->getInrToUsdRate();
            return [
                'rate' => $rate,
                'inr_per_usd' => round(1 / $rate, 2),
                'formatted' => '1 INR = $' . number_format($rate, 4),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear cached rates (useful for testing or manual refresh)
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('Currency rate cache cleared');
    }
}
