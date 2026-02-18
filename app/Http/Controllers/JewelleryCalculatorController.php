<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JewelleryCalculatorController extends Controller
{
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
     * Cached for 60 seconds to prevent rate limiting and improve performance.
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

            // --- 2. Gold Rate via Cloudflare Worker Proxy → Navkar MCX ---
            $goldUsdPerGram = Cache::remember('gold_rate_usd_gram_v5', 1, function () use ($usdRate) {

                // ── PRIMARY: Navkar via Cloudflare Worker (Indian MCX rate) ────
                try {
                    $r = Http::withoutVerifying()->timeout(10)
                        ->withHeaders(['X-Proxy-Key' => 'navkar-proxy-xK9mP2024'])
                        ->get('https://navkar-gold-proxy.minimalcarbonstore.workers.dev');

                    if ($r->successful()) {
                        $content = trim($r->body());
                        $inrPer10g = 0;

                        // TSV format parsing
                        foreach (explode("\n", $content) as $line) {
                            $line = trim($line);
                            if (strpos($line, 'GOLD 999 IMP') !== false || strpos($line, 'GOLD 999 10GM') !== false) {
                                $parts = preg_split('/\s+|\t/', $line);
                                foreach ($parts as $part) {
                                    $part = str_replace(',', '', trim($part));
                                    if (is_numeric($part) && floatval($part) > 50000) {
                                        $inrPer10g = floatval($part);
                                        break 2;
                                    }
                                }
                            }
                        }

                        if ($inrPer10g > 50000) {
                            $perGram = ($inrPer10g / 10) * $usdRate;
                            Log::info('Gold via Navkar Proxy: ₹' . ($inrPer10g / 10) . '/g = $' . round($perGram, 2));
                            return $perGram;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Navkar Proxy failed: ' . $e->getMessage());
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
