<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'jewellery_pricing.platinum_950_rate_usd_per_gram'],
            ['value' => '30', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('app_settings')
            ->where('key', 'jewellery_pricing.silver_925_rate_usd_per_gram')
            ->delete();
    }

    public function down(): void
    {
        $now = now();

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'jewellery_pricing.silver_925_rate_usd_per_gram'],
            ['value' => '0', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('app_settings')
            ->where('key', 'jewellery_pricing.platinum_950_rate_usd_per_gram')
            ->delete();
    }
};
