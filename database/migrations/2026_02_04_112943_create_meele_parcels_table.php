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
        Schema::create('meele_parcels', function (Blueprint $table) {
            $table->id();
            $table->string('parcel_code', 32)->unique();
            $table->string('sieve_size', 32);
            $table->enum('category', ['Stars', 'Meele', 'Coarse']);

            // Stock Levels - Denormalized but maintained via Service/Transactions
            $table->unsignedInteger('current_pieces')->default(0);
            $table->decimal('current_weight', 12, 4)->default(0.0000); // 4 decimal precision

            // Valuation
            $table->decimal('avg_cost_per_carat', 12, 2)->nullable();

            $table->enum('status', ['active', 'archived', 'out_of_stock'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meele_parcels');
    }
};
