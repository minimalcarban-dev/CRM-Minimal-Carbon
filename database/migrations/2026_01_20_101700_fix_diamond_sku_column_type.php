<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration fixes the diamond_sku column type.
     * It was incorrectly set as JSON but should be VARCHAR for backward compatibility.
     */
    public function up(): void
    {
        // First, convert any JSON values in diamond_sku to strings
        // Get all orders with JSON diamond_sku values and extract the first value
        $orders = DB::table('orders')
            ->whereNotNull('diamond_sku')
            ->where('diamond_sku', '!=', '')
            ->get(['id', 'diamond_sku']);

        foreach ($orders as $order) {
            $sku = $order->diamond_sku;
            // If it's a JSON array, extract first element
            if (is_string($sku) && str_starts_with($sku, '[')) {
                $decoded = json_decode($sku, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $sku = $decoded[0];
                } else {
                    $sku = '';
                }
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['diamond_sku' => $sku]);
            }
        }

        // Now change column type from JSON to VARCHAR
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY diamond_sku VARCHAR(191) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to JSON type (not recommended but provided for rollback)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY diamond_sku JSON NULL');
        }
    }
};
