<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\GoldPurchase;
use App\Models\GoldRateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoldTrackingRateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSuperAdmin(): Admin
    {
        return Admin::query()->create([
            'name' => 'Gold Super',
            'email' => 'gold-super-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone_number' => '9999999999',
            'is_super' => true,
        ]);
    }

    public function test_rate_api_returns_unavailable_for_past_date_without_snapshot(): void
    {
        $admin = $this->makeSuperAdmin();
        $date = now()->subDays(5)->toDateString();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('gold-tracking.rate', ['date' => $date]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'date' => $date,
                'is_available' => false,
                'is_live' => false,
            ]);
    }

    public function test_rate_api_returns_snapshot_for_past_date(): void
    {
        $admin = $this->makeSuperAdmin();
        $date = now()->subDays(3)->toDateString();

        GoldRateSnapshot::query()->create([
            'rate_date' => $date,
            'inr_per_gram' => 7399.50,
            'inr_per_10g' => 73995.00,
            'source' => 'manual_snapshot',
            'fetched_at' => now()->subDays(3),
            'is_live' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('gold-tracking.rate', ['date' => $date]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'date' => $date,
                'rate_inr_per_gram' => 7399.50,
                'rate_inr_per_10g' => 73995.00,
                'is_available' => true,
                'is_live' => false,
            ]);
    }

    public function test_outlier_rate_requires_confirmation_when_snapshot_exists(): void
    {
        $admin = $this->makeSuperAdmin();
        $date = now()->subDays(2)->toDateString();

        GoldRateSnapshot::query()->create([
            'rate_date' => $date,
            'inr_per_gram' => 7500.00,
            'inr_per_10g' => 75000.00,
            'source' => 'manual_snapshot',
            'fetched_at' => now()->subDays(2),
            'is_live' => false,
        ]);

        $payload = [
            'purchase_date' => $date,
            'weight_grams' => 5,
            'rate_per_gram' => 156000,
            'supplier_name' => 'Test Supplier',
            'supplier_mobile' => '9999999999',
            'invoice_number' => 'INV-OUTLIER-1',
            'payment_mode' => 'cash',
            'notes' => 'Outlier test',
        ];

        $this->actingAs($admin, 'admin')
            ->post(route('gold-tracking.purchases.store'), $payload)
            ->assertSessionHasErrors(['rate_per_gram', 'confirm_outlier_rate']);

        $this->assertDatabaseCount('gold_purchases', 0);

        $payload['confirm_outlier_rate'] = 1;

        $this->actingAs($admin, 'admin')
            ->post(route('gold-tracking.purchases.store'), $payload)
            ->assertRedirect(route('gold-tracking.index'));

        $this->assertDatabaseCount('gold_purchases', 1);
        $this->assertDatabaseHas('gold_purchases', [
            'supplier_name' => 'Test Supplier',
            'rate_per_gram' => 156000.00,
        ]);

        $purchase = GoldPurchase::query()->first();
        $this->assertNotNull($purchase);
        $this->assertEquals(780000.00, (float) $purchase->total_amount);
    }
}
