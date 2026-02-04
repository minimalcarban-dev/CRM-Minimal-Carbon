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
            $table->foreignId('meele_diamond_id')->nullable()->constrained('meele_parcels')->nullOnDelete();
            $table->integer('meele_pieces')->nullable();
            $table->decimal('meele_carat', 12, 4)->nullable();
            $table->decimal('meele_total_value', 14, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
