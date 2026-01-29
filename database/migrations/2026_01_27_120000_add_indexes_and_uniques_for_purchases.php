<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Common filters / reporting
            $table->index('purchase_date');
            $table->index('status');
            $table->index('payment_mode');
            $table->index('party_id');
            $table->index('admin_id');
            $table->index(['status', 'purchase_date']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            // Enforce 1:1 for auto-linked purchase expenses (allows multiple NULLs)
            if (!Schema::hasColumn('expenses', 'purchase_id')) {
                return;
            }
            $table->unique('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'purchase_id')) {
                $table->dropUnique(['purchase_id']);
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['purchase_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_mode']);
            $table->dropIndex(['party_id']);
            $table->dropIndex(['admin_id']);
            $table->dropIndex(['status', 'purchase_date']);
        });
    }
};
