<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration adds:
     * - status column for pending/completed tracking
     * - bank_transfer option to payment_mode
     * - bank details fields for bank transfer payments
     * - expense_id for linking to auto-created expense
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Add status column - default to 'completed' so existing records remain valid
            $table->enum('status', ['pending', 'completed'])->default('completed')->after('id');

            // Add bank transfer details fields
            $table->string('bank_account_name')->nullable()->after('upi_id');
            $table->string('bank_name')->nullable()->after('bank_account_name');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_ifsc', 20)->nullable()->after('bank_account_number');

            // Add foreign key to link with auto-created expense
            $table->foreignId('expense_id')->nullable()->after('admin_id')->constrained('expenses')->nullOnDelete();
        });

        // Modify payment_mode enum to include bank_transfer and make nullable
        // Using raw SQL as Laravel doesn't support modifying enums directly
        DB::statement("ALTER TABLE purchases MODIFY COLUMN payment_mode ENUM('upi', 'cash', 'bank_transfer') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['expense_id']);
            $table->dropColumn('expense_id');

            // Drop bank details columns
            $table->dropColumn(['bank_account_name', 'bank_name', 'bank_account_number', 'bank_ifsc']);

            // Drop status column
            $table->dropColumn('status');
        });

        // Revert payment_mode back to original enum (only if no bank_transfer records exist)
        DB::statement("ALTER TABLE purchases MODIFY COLUMN payment_mode ENUM('upi', 'cash') NOT NULL");
    }
};
