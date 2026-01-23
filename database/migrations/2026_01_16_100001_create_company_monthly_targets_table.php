<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Creates table for storing monthly sales targets per company.
     */
    public function up(): void
    {
        Schema::create('company_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('target_amount', 12, 2)->default(0);
            $table->timestamps();

            // Unique constraint: one target per company per month
            $table->unique(['company_id', 'year', 'month'], 'company_month_target_unique');
            $table->index(['year', 'month'], 'idx_target_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_monthly_targets');
    }
};
