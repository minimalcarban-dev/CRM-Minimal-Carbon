<?php

namespace App\Http\Controllers;

use App\Services\GoldRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JewelleryCalculatorController extends Controller
{
    private const GOLD_RATE_CACHE_SECONDS = 30;

    public function __construct(
        protected GoldRateService $goldRateService
    ) {
    }

    /**
     * Display the calculator page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.tools.jewellery-calculator');
    }

    /**
     * Fetch live gold rates and currency exchange rates.
     * Returns a JSON response with the calculated gold rate per gram in USD.
     * Cached for 30 seconds to prevent rate limiting and improve performance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRates()
    {
        try {
            // --- 1. Currency API (Cache 1 Hour) ---
            $usdRate = Cache::remember('currency_usd_inr_v2', 3600, function () {
                try {
                    $r = Http::withoutVerifying()->timeout(8)
                        ->get('https://currency-rate-exchange-api.onrender.com/inr');
                    if ($r->successful()) {
                        $rates = $r->json('rates');
                        if (isset($rates['inr']['usd']))
                            return floatval($rates['inr']['usd']);
                        if (isset($rates['USD']))
                            return floatval($rates['USD']);
                    }
                } catch (\Exception $e) {
                    Log::warning('Currency API failed: ' . $e->getMessage());
                }
                return 0.0118;
            });

            // --- 2. Gold Rate via GoldRateService (Navkar INR -> USD conversion) ---
            $goldUsdPerGram = Cache::remember('gold_rate_usd_gram_v5', self::GOLD_RATE_CACHE_SECONDS, function () use ($usdRate) {
                $todayRate = $this->goldRateService->getRateForDate(now()->toDateString());
                if (($todayRate['is_available'] ?? false) && ($todayRate['rate_inr_per_gram'] ?? 0) > 0) {
                    $perGram = (float) $todayRate['rate_inr_per_gram'] * $usdRate;
                    Log::info('Gold via GoldRateService: ₹' . $todayRate['rate_inr_per_gram'] . '/g = $' . round($perGram, 2));
                    return $perGram;
                }

                // ── FALLBACK: Coinbase + India premium 8.5% ────────────────────
                try {
                    $r = Http::withoutVerifying()->timeout(8)
                        ->get('https://api.coinbase.com/v2/exchange-rates?currency=USD');

                    if ($r->successful()) {
                        $xauPerUsd = floatval($r->json('data.rates.XAU') ?? 0);
                        if ($xauPerUsd > 0) {
                            $intlPerGram = (1 / $xauPerUsd) / 31.1035;
                            $indianPerGram = $intlPerGram * 1.085;
                            Log::info('Gold via Coinbase fallback: $' . round($indianPerGram, 2));
                            return $indianPerGram;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Coinbase fallback failed: ' . $e->getMessage());
                }

                return 0;
            });

            // --- 3. Fallback ---
            if ($goldUsdPerGram <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Live Gold Rate Unavailable',
                    'error' => 'All APIs failed',
                ]);
            }

            // --- 4. Return ---
            return response()->json([
                'success' => true,
                'rate' => round($goldUsdPerGram, 2),
                'currency' => 'USD',
                'timestamp' => now()->toIso8601String(),
                'source' => 'live',
            ]);

        } catch (\Exception $e) {
            Log::critical('JewelleryCalculator CRITICAL: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }
}
