<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Nullable because not all orders utilize melee diamonds
            $table->foreignId('melee_diamond_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('melee_pieces')->nullable();
            $table->decimal('melee_carat', 10, 3)->nullable();
            $table->decimal('melee_price_per_ct', 12, 2)->nullable();
            $table->decimal('melee_total_value', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['melee_diamond_id']);
            $table->dropColumn([
                'melee_diamond_id',
                'melee_pieces',
                'melee_carat',
                'melee_price_per_ct',
                'melee_total_value'
            ]);
        });
    }
};
