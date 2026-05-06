<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add price fields to jewellery_stocks (primary stone) and
     * jewellery_stock_side_stones tables.
     */
    public function up(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->decimal('primary_stone_price', 12, 2)->nullable()->after('primary_stone_weight');
        });

        Schema::table('jewellery_stock_side_stones', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->dropColumn('primary_stone_price');
        });

        Schema::table('jewellery_stock_side_stones', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
