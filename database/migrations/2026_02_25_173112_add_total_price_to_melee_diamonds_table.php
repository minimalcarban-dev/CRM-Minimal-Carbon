<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('melee_diamonds', function (Blueprint $table) {
            $table->decimal('total_price', 12, 2)->default(0)->after('purchase_price_per_ct');
        });

        // Backfill existing rows: total_price = available_carat_weight * purchase_price_per_ct
        if (DB::table('melee_diamonds')->count() > 0) {
            DB::statement('UPDATE melee_diamonds SET total_price = available_carat_weight * purchase_price_per_ct');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('melee_diamonds', function (Blueprint $table) {
            $table->dropColumn('total_price');
        });
    }
};
