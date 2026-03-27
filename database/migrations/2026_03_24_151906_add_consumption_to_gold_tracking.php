<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the 'consumed' enum value to gold_distributions
        // We use a raw statement because changing ENUMs via Blueprint can be problematic in older Laravel/MySQL
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE gold_distributions MODIFY COLUMN type ENUM('out', 'return', 'consumed') NOT NULL DEFAULT 'out'");
        }

        Schema::table('gold_distributions', function (Blueprint $table) {
            if (!Schema::hasColumn('gold_distributions', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('factory_id')->constrained('orders')->onDelete('set null');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'gold_net_weight')) {
                // Typical jewelry accuracy to 3 decimal places
                $table->decimal('gold_net_weight', 8, 3)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'gold_net_weight')) {
                $table->dropColumn('gold_net_weight');
            }
        });

        Schema::table('gold_distributions', function (Blueprint $table) {
            if (Schema::hasColumn('gold_distributions', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
        });

        // Reverting enum changes requires raw SQL too
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE gold_distributions MODIFY COLUMN type ENUM('out', 'return') NOT NULL DEFAULT 'out'");
        }
        
        \Illuminate\Support\Facades\DB::table('permissions')->where('slug', 'orders.add_gold_weight')->delete();
    }
};
