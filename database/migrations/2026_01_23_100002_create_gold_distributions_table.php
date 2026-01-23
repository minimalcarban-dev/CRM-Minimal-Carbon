<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Creates the gold_distributions table for Gold Tracking module.
     * Tracks gold distribution to factories and returns from factories.
     * Type 'out' = sent to factory, 'return' = returned from factory.
     */
    public function up(): void
    {
        Schema::create('gold_distributions', function (Blueprint $table) {
            $table->id();
            $table->date('distribution_date');
            $table->foreignId('factory_id')->constrained('factories')->onDelete('restrict');
            $table->decimal('weight_grams', 10, 3); // e.g., 10.500 grams
            $table->enum('type', ['out', 'return'])->default('out'); // out = to factory, return = from factory
            $table->string('purpose')->nullable(); // Free text: Ring Production, Jewellery Making, etc.
            $table->text('notes')->nullable();

            // Who made this distribution
            $table->foreignId('admin_id')->constrained('admins')->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('distribution_date');
            $table->index('type');
            $table->index(['factory_id', 'type']);
            $table->index(['distribution_date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_distributions');
    }
};
