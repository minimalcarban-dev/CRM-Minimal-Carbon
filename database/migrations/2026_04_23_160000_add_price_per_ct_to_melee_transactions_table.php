<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('melee_transactions', function (Blueprint $table) {
            $table->decimal('price_per_ct', 12, 2)->nullable()->after('carat_weight');
        });

        $diamondPrices = DB::table('melee_diamonds')
            ->pluck('purchase_price_per_ct', 'id');

        DB::table('melee_transactions')
            ->whereNull('price_per_ct')
            ->orderBy('id')
            ->chunkById(500, function ($transactions) use ($diamondPrices) {
                foreach ($transactions as $transaction) {
                    $fallbackPrice = (float) ($diamondPrices[$transaction->melee_diamond_id] ?? 0);

                    DB::table('melee_transactions')
                        ->where('id', $transaction->id)
                        ->update(['price_per_ct' => $fallbackPrice]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('melee_transactions', function (Blueprint $table) {
            $table->dropColumn('price_per_ct');
        });
    }
};
