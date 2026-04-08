<?php

use App\Models\Diamond;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeSyncDiamond(array $overrides = []): Diamond
{
    static $seq = 2000;
    $seq++;

    return Diamond::create(array_merge([
        'lot_no' => 'LOT-SYNC-' . $seq,
        'sku' => 'SKU-SYNC-' . $seq,
        'barcode_number' => 'BC-SYNC-' . $seq,
        'purchase_price' => 120,
        'margin' => 10,
    ], $overrides));
}

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-04-08 09:00:00', config('app.timezone', 'Asia/Kolkata')));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('supports dry-run without writing data', function () {
    $diamond = makeSyncDiamond([
        'purchase_date' => '2026-03-27',
        'sold_out_date' => null,
        'is_sold_out' => 'IN Stock',
    ]);

    DB::table('diamonds')->where('id', $diamond->id)->update([
        'duration_days' => 1,
        'duration_price' => 120.05,
    ]);

    $this->artisan('diamonds:sync-duration --dry-run --chunk=50')
        ->expectsOutputToContain('DRY RUN')
        ->expectsOutputToContain('Rows changed: 1')
        ->assertExitCode(0);

    $fresh = Diamond::findOrFail($diamond->id);
    expect((int) $fresh->getRawOriginal('duration_days'))->toBe(1)
        ->and(round((float) $fresh->getRawOriginal('duration_price'), 2))->toBe(120.05);
});

it('updates stale snapshot data and normalizes invalid sold status', function () {
    $stale = makeSyncDiamond([
        'purchase_date' => '2026-03-27',
        'sold_out_date' => null,
        'is_sold_out' => 'IN Stock',
        'purchase_price' => 100,
    ]);

    $invalidSold = makeSyncDiamond([
        'purchase_date' => '2025-01-21',
        'sold_out_date' => null,
        'is_sold_out' => 'Sold',
        'purchase_price' => 150,
    ]);

    DB::table('diamonds')->where('id', $stale->id)->update([
        'duration_days' => 1,
        'duration_price' => 100.05,
    ]);

    DB::table('diamonds')->where('id', $invalidSold->id)->update([
        'is_sold_out' => 'Sold',
        'duration_days' => 10,
        'duration_price' => 150.75,
        'sold_out_month' => '2026-02',
    ]);

    $this->artisan('diamonds:sync-duration --chunk=100')
        ->expectsOutputToContain('Rows changed: 2')
        ->expectsOutputToContain('Invalid Sold->IN Stock normalized: 1')
        ->assertExitCode(0);

    $staleFresh = Diamond::findOrFail($stale->id);
    $invalidFresh = Diamond::findOrFail($invalidSold->id);

    $expectedStaleDays = 12;
    $expectedStalePrice = round(100 * pow(1 + 0.0005, $expectedStaleDays), 2);

    expect((int) $staleFresh->getRawOriginal('duration_days'))->toBe($expectedStaleDays)
        ->and(round((float) $staleFresh->getRawOriginal('duration_price'), 2))->toBe($expectedStalePrice)
        ->and($invalidFresh->getRawOriginal('is_sold_out'))->toBe('IN Stock')
        ->and($invalidFresh->getRawOriginal('sold_out_month'))->toBeNull();
});

it('is idempotent on repeated runs', function () {
    makeSyncDiamond([
        'purchase_date' => '2026-03-27',
        'sold_out_date' => null,
        'purchase_price' => 100,
    ]);

    $this->artisan('diamonds:sync-duration --chunk=100')->assertExitCode(0);

    $this->artisan('diamonds:sync-duration --chunk=100')
        ->expectsOutputToContain('Rows changed: 0')
        ->assertExitCode(0);
});
