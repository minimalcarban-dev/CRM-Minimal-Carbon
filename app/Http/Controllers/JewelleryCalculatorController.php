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
            // Log start of request for debugging (Comment out for high frequency log spam)
            // Log::info('JewelleryCalculator: Starting Rate Fetch');

            // --- 1. Currency API (Cache for 1 Hour) ---
            // Currency rates don't fluctuate maniacally like gold.
            $usdRate = Cache::remember('currency_usd_inr_v1', 3600, function () {
                try {
                    // Log::info('JewelleryCalculator: Fetching Currency from API...');
                    $exResponse = Http::withoutVerifying()->timeout(5)->get('https://currency-rate-exchange-api.onrender.com/inr');

                    if ($exResponse->successful()) {
                        $rates = $exResponse->json('rates');
                        if (isset($rates['inr']['usd'])) {
                            return floatval($rates['inr']['usd']);
                        } elseif (isset($rates['USD'])) {
                            return floatval($rates['USD']);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('JewelleryCalculator: Currency API Exception: ' . $e->getMessage());
                }
                return 0.0118; // Default fallback
            });

            // --- 2. Gold API (LIVE - No Cache) ---
            // Fetch every time for real-time updates
            $goldRateInr10g = 0;

            try {
                // Log::info('JewelleryCalculator: Fetching Gold Rate...');
                // Note: Port 7768 might be blocked on some hosts. 
                $goldResponse = Http::withoutVerifying()->timeout(5)->get('https://bcast.navkargold.com:7768/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/navkar');

                if ($goldResponse->successful()) {
                    $content = trim($goldResponse->body());

                    // Check format: XML vs Text/TSV
                    if (strpos($content, '<') === 0 && strpos($content, '<xml') !== false) {
                        libxml_use_internal_errors(true);
                        $xml = simplexml_load_string($content);
                        if ($xml && isset($xml->row)) {
                            foreach ($xml->row as $row) {
                                $symbol = (string) $row['symbol_name'];
                                if (strpos($symbol, 'GOLD 999 10GM') !== false || strpos($symbol, 'GOLD 999 IMP') !== false) {
                                    $price = str_replace(',', '', (string) $row['bid_price']);
                                    if (is_numeric($price) && $price > 0) {
                                        $goldRateInr10g = floatval($price);
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        // Text/TSV Value Parsing
                        // Log::info("JewelleryCalculator: Parsing Text Response (Length: " . strlen($content) . ")");
                        $lines = explode("\n", $content);

                        $gold999Found = false;
                        $goldDotValue = 0; // Fallback "GOLD." value

                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (empty($line))
                                continue;

                            // 1. Attempt to find "GOLD 999 IMP" or similar
                            if (strpos($line, 'GOLD 999 IMP') !== false || strpos($line, 'GOLD 999 10GM') !== false) {
                                $parts = preg_split('/\s+/', $line);
                                foreach ($parts as $part) {
                                    if (is_numeric($part) && floatval($part) > 50000) {
                                        $goldRateInr10g = floatval($part);
                                        $gold999Found = true;
                                        break;
                                    }
                                }
                            }

                            // 2. Fallback: Parse "GOLD." (e.g. 4910.80)
                            if (!$gold999Found && strpos($line, 'GOLD.') !== false) {
                                $parts = preg_split('/\s+/', $line);
                                foreach ($parts as $part) {
                                    if (is_numeric($part) && floatval($part) > 3000 && floatval($part) < 8000) {
                                        $goldDotValue = floatval($part);
                                        break;
                                    }
                                }
                            }

                            if ($gold999Found)
                                break;
                        }

                        // If explicit 10g rate not found, use heuristic
                        if (!$gold999Found && $goldDotValue > 0) {
                            $goldRateInr10g = $goldDotValue * 31.87;
                        }
                    }

                } else {
                    Log::error('JewelleryCalculator: Gold API failed status: ' . $goldResponse->status());
                }
            } catch (\Exception $e) {
                Log::error('JewelleryCalculator: Gold API Exception: ' . $e->getMessage());
            }

            // --- 3. Fallback Logic ---
            if ($goldRateInr10g <= 0) {
                // User requested NO hardcoded fallback. 
                // If API fails, we return an error state so the frontend can show "Unavailable".
                return response()->json([
                    'success' => false,
                    'message' => 'Live Gold Rate Unavailable',
                    'error' => 'API returned no valid data'
                ]);
            }

            // --- 4. Calculation ---
            // 10g INR -> 1g INR
            $goldRateInr1g = $goldRateInr10g / 10;
            // 1g INR -> 1g USD
            $goldRateUsd1g = $goldRateInr1g * $usdRate;

            $finalRate = round($goldRateUsd1g, 2);

            return response()->json([
                'success' => true,
                'rate' => $finalRate,
                'currency' => 'USD',
                'timestamp' => now()->toIso8601String(),
                'source' => 'live'
            ]);

        } catch (\Exception $e) {
            Log::critical('JewelleryCalculator: CRITICAL UNHANDLED: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
