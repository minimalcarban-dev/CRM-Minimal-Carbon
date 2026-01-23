<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Adds gold_purchase_id to expenses table for linking gold purchases
     * to their auto-created expense entries (similar to purchase_id for diamond purchases).
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Add gold_purchase_id for linking to gold purchases
            // Using after() to place it next to purchase_id for logical grouping
            $table->foreignId('gold_purchase_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('gold_purchases')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['gold_purchase_id']);
            $table->dropColumn('gold_purchase_id');
        });
    }
};
