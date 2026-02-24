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
        Schema::create('global_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('target_amount', 12, 2)->default(0);
            $table->timestamps();

            // Unique constraint: one target per month per year
            $table->unique(['year', 'month'], 'global_month_target_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_monthly_targets');
    }
};
