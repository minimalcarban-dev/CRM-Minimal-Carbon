<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration converts the diamond_sku column from a single string value
     * to a JSON array to support multiple diamond SKUs per order.
     * 
     * BACKWARD COMPATIBLE: Existing single SKU values are converted to arrays.
     */
    public function up(): void
    {
        // Step 1: Add a new temporary column for JSON storage
        Schema::table('orders', function (Blueprint $table) {
            $table->json('diamond_skus')->nullable()->after('diamond_sku')
                ->comment('Array of diamond SKUs for this order');
        });

        // Step 2: Migrate existing single SKU values to the new JSON column
        // Convert single string like "SKU123" to array ["SKU123"]
        DB::table('orders')
            ->whereNotNull('diamond_sku')
            ->where('diamond_sku', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update([
                            'diamond_skus' => json_encode([$order->diamond_sku])
                        ]);
                }
            });

        // Step 3: Keep the old diamond_sku column for backward compatibility
        // (It will be deprecated but not removed to avoid breaking existing queries)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('diamond_skus');
        });
    }
};
