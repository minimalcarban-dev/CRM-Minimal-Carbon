<?php

namespace App\Services;

use App\Models\GoldRateSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoldRateService
{
    private const NAVKAR_PROXY_URL = 'https://navkar-gold-proxy.minimalcarbonstore.workers.dev';
    private const NAVKAR_PROXY_KEY = 'navkar-proxy-xK9mP2024';
    private const LIVE_CACHE_SECONDS = 60;

    /**
     * Get gold rate for a specific date.
     *
     * Contract:
     * - success: bool (request processed and contract produced)
     * - is_available: bool (rate present for selected date)
     * - date: YYYY-MM-DD
     * - rate_inr_per_gram: float|null
     * - rate_inr_per_10g: float|null
     * - source: string|null
     * - is_live: bool
     * - message: string|null
     */
    public function getRateForDate(string $date): array
    {
        $targetDate = Carbon::parse($date)->toDateString();
        $today = now()->toDateString();

        if ($targetDate > $today) {
            return [
                'success' => false,
                'date' => $targetDate,
                'rate_inr_per_gram' => null,
                'rate_inr_per_10g' => null,
                'source' => null,
                'is_live' => false,
                'is_available' => false,
                'message' => 'Future date is not allowed.',
            ];
        }

        if ($targetDate === $today) {
            $live = $this->fetchLiveInrRate();

            if ($live['success']) {
                $this->upsertSnapshot(
                    $targetDate,
                    (float) $live['rate_inr_per_gram'],
                    (float) $live['rate_inr_per_10g'],
                    (string) $live['source'],
                    true
                );

                return [
                    'success' => true,
                    'date' => $targetDate,
                    'rate_inr_per_gram' => round((float) $live['rate_inr_per_gram'], 2),
                    'rate_inr_per_10g' => round((float) $live['rate_inr_per_10g'], 2),
                    'source' => (string) $live['source'],
                    'is_live' => true,
                    'is_available' => true,
                    'message' => null,
                ];
            }

            $snapshot = GoldRateSnapshot::query()
                ->whereDate('rate_date', $targetDate)
                ->first();

            if ($snapshot) {
                return $this->snapshotPayload(
                    $snapshot,
                    false,
                    'Live rate unavailable, using last stored snapshot for today.'
                );
            }

            return [
                'success' => false,
                'date' => $targetDate,
                'rate_inr_per_gram' => null,
                'rate_inr_per_10g' => null,
                'source' => null,
                'is_live' => false,
                'is_available' => false,
                'message' => $live['message'] ?? 'Live gold rate unavailable.',
            ];
        }

        $snapshot = GoldRateSnapshot::query()
            ->whereDate('rate_date', $targetDate)
            ->first();

        if (!$snapshot) {
            return [
                'success' => true,
                'date' => $targetDate,
                'rate_inr_per_gram' => null,
                'rate_inr_per_10g' => null,
                'source' => null,
                'is_live' => false,
                'is_available' => false,
                'message' => 'No stored rate for this date. Enter manually.',
            ];
        }

        return $this->snapshotPayload($snapshot, false, null);
    }

    /**
     * Fetch live INR gold rate from Navkar proxy.
     */
    public function fetchLiveInrRate(): array
    {
        return Cache::remember('gold_rate_live_inr_v1', self::LIVE_CACHE_SECONDS, function () {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(10)
                    ->withHeaders(['X-Proxy-Key' => self::NAVKAR_PROXY_KEY])
                    ->get(self::NAVKAR_PROXY_URL);

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'message' => 'Navkar proxy request failed.',
                    ];
                }

                $inrPer10g = $this->extractInrPer10g($response->body());
                if ($inrPer10g <= 0) {
                    return [
                        'success' => false,
                        'message' => 'Unable to parse live Navkar gold rate.',
                    ];
                }

                return [
                    'success' => true,
                    'rate_inr_per_10g' => round($inrPer10g, 2),
                    'rate_inr_per_gram' => round($inrPer10g / 10, 2),
                    'source' => 'navkar_live',
                ];
            } catch (\Throwable $e) {
                Log::warning('GoldRateService live fetch failed: ' . $e->getMessage());

                return [
                    'success' => false,
                    'message' => 'Live gold rate unavailable.',
                ];
            }
        });
    }

    /**
     * Flag suspicious entered rate using expected baseline rate.
     */
    public function isOutlierRate(float $enteredRate, float $expectedRate, float $minFactor = 0.70, float $maxFactor = 1.30): bool
    {
        if ($enteredRate <= 0 || $expectedRate <= 0) {
            return false;
        }

        return $enteredRate < ($expectedRate * $minFactor)
            || $enteredRate > ($expectedRate * $maxFactor);
    }

    protected function extractInrPer10g(string $content): float
    {
        $inrPer10g = 0.0;
        $lines = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (stripos($line, 'GOLD 999 IMP') === false && stripos($line, 'GOLD 999 10GM') === false) {
                continue;
            }

            $parts = preg_split('/\s+|\t/', $line) ?: [];
            foreach ($parts as $part) {
                $numeric = str_replace(',', '', trim($part));
                if (!is_numeric($numeric)) {
                    continue;
                }

                $value = (float) $numeric;
                if ($value > 50000) {
                    $inrPer10g = $value;
                    break 2;
                }
            }
        }

        return $inrPer10g;
    }

    protected function upsertSnapshot(
        string $rateDate,
        float $inrPerGram,
        float $inrPer10g,
        string $source,
        bool $isLive
    ): void {
        GoldRateSnapshot::query()->updateOrCreate(
            ['rate_date' => $rateDate],
            [
                'inr_per_gram' => round($inrPerGram, 2),
                'inr_per_10g' => round($inrPer10g, 2),
                'source' => $source,
                'fetched_at' => now(),
                'is_live' => $isLive,
            ]
        );
    }

    protected function snapshotPayload(GoldRateSnapshot $snapshot, bool $isLive, ?string $message): array
    {
        return [
            'success' => true,
            'date' => $snapshot->rate_date?->toDateString(),
            'rate_inr_per_gram' => round((float) $snapshot->inr_per_gram, 2),
            'rate_inr_per_10g' => round((float) $snapshot->inr_per_10g, 2),
            'source' => (string) $snapshot->source,
            'is_live' => $isLive,
            'is_available' => true,
            'message' => $message,
        ];
    }
}
