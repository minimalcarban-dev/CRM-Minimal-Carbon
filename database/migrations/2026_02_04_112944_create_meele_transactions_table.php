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
        Schema::create('meele_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meele_parcel_id')->constrained('meele_parcels');
            $table->foreignId('user_id')->constrained('users'); // Auditor/Admin

            $table->enum('type', ['purchase', 'sale', 'adjustment_add', 'adjustment_sub', 'return', 'initial']);

            // Polymorphic relation to Order, Purchase, etc.
            $table->nullableMorphs('reference');

            // Movement
            $table->integer('pieces'); // Can be negative
            $table->decimal('weight', 12, 4); // Can be negative

            // Financials
            $table->decimal('price_per_carat', 12, 2)->nullable();
            $table->decimal('total_value', 14, 2)->nullable();

            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at for immutable ledger
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meele_transactions');
    }
};
