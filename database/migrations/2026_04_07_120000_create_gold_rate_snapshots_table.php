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
        Schema::create('gold_rate_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('rate_date')->unique();
            $table->decimal('inr_per_gram', 12, 2);
            $table->decimal('inr_per_10g', 12, 2);
            $table->string('source', 100)->default('navkar');
            $table->timestamp('fetched_at');
            $table->boolean('is_live')->default(false);
            $table->timestamps();

            $table->index('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_rate_snapshots');
    }
};
