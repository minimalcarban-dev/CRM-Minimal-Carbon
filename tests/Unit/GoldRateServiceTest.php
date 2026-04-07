<?php

namespace Tests\Unit;

use App\Models\GoldRateSnapshot;
use App\Services\GoldRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoldRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fetches_today_rate_and_stores_snapshot(): void
    {
        Cache::flush();

        Http::fake([
            'https://navkar-gold-proxy.minimalcarbonstore.workers.dev' => Http::response(
                "GOLD 999 IMP\t76,543\t+100\nSILVER\t88,000",
                200
            ),
        ]);

        $service = app(GoldRateService::class);
        $today = now()->toDateString();
        $payload = $service->getRateForDate($today);

        $this->assertTrue($payload['success']);
        $this->assertTrue($payload['is_available']);
        $this->assertTrue($payload['is_live']);
        $this->assertEquals(7654.30, $payload['rate_inr_per_gram']);
        $this->assertEquals(76543.00, $payload['rate_inr_per_10g']);

        $this->assertTrue(
            GoldRateSnapshot::query()
                ->whereDate('rate_date', $today)
                ->where('source', 'navkar_live')
                ->where('is_live', true)
                ->exists()
        );
    }

    public function test_it_returns_past_snapshot_without_hitting_live_api(): void
    {
        Cache::flush();

        $yesterday = now()->subDay()->toDateString();
        GoldRateSnapshot::query()->create([
            'rate_date' => $yesterday,
            'inr_per_gram' => 7421.55,
            'inr_per_10g' => 74215.50,
            'source' => 'manual_seed',
            'fetched_at' => now()->subDay(),
            'is_live' => false,
        ]);

        Http::fake();

        $service = app(GoldRateService::class);
        $payload = $service->getRateForDate($yesterday);

        $this->assertTrue($payload['success']);
        $this->assertTrue($payload['is_available']);
        $this->assertFalse($payload['is_live']);
        $this->assertEquals(7421.55, $payload['rate_inr_per_gram']);
        $this->assertEquals(74215.50, $payload['rate_inr_per_10g']);

        Http::assertNothingSent();
    }
}
