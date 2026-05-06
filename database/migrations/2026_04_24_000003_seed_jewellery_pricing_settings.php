<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $settings = [
            'jewellery_pricing.labor_rate_usd_per_gram' => '20',
            'jewellery_pricing.default_commission_percent' => '20',
            'jewellery_pricing.default_profit_percent' => '25',
            'jewellery_pricing.default_sales_markup_percent' => '0',
            'jewellery_pricing.silver_925_rate_usd_per_gram' => '0',
        ];

        foreach ($settings as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('app_settings')
            ->whereIn('key', [
                'jewellery_pricing.labor_rate_usd_per_gram',
                'jewellery_pricing.default_commission_percent',
                'jewellery_pricing.default_profit_percent',
                'jewellery_pricing.default_sales_markup_percent',
                'jewellery_pricing.silver_925_rate_usd_per_gram',
            ])
            ->delete();
    }
};
