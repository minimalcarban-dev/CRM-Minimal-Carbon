<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Store individual prices for each diamond SKU
            // Format: {"SK-12345": 500.00, "SK-67890": 350.00}
            $table->json('diamond_prices')->nullable()->after('diamond_skus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('diamond_prices');
        });
    }
};
