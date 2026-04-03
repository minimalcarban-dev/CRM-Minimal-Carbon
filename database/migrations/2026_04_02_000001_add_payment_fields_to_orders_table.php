<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['full', 'partial', 'due'])
                    ->nullable()
                    ->after('gross_sell')
                    ->comment('Payment completion state');
            }

            if (!Schema::hasColumn('orders', 'amount_received')) {
                $table->decimal('amount_received', 10, 2)
                    ->nullable()
                    ->after('payment_status')
                    ->comment('Amount collected against this order');
            }

            if (!Schema::hasColumn('orders', 'amount_due')) {
                $table->decimal('amount_due', 10, 2)
                    ->nullable()
                    ->after('amount_received')
                    ->comment('Outstanding amount remaining on this order');
            }
        });

        DB::table('orders')
            ->whereNull('payment_status')
            ->update([
                'payment_status' => 'full',
                'amount_received' => DB::raw('COALESCE(gross_sell, 0)'),
                'amount_due' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'amount_due')) {
                $table->dropColumn('amount_due');
            }
            if (Schema::hasColumn('orders', 'amount_received')) {
                $table->dropColumn('amount_received');
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
