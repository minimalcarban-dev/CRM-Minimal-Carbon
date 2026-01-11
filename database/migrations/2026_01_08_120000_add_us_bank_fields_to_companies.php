<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Add US-specific bank fields and currency for companies
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // US Bank Details for Wire Transfers
            $table->string('beneficiary_name')->nullable()->after('account_holder_name');
            $table->string('aba_routing_number', 9)->nullable()->after('beneficiary_name');
            $table->string('us_account_no', 50)->nullable()->after('aba_routing_number');
            $table->enum('account_type', ['checking', 'savings'])->nullable()->after('us_account_no');
            $table->text('bank_address')->nullable()->after('account_type');

            // Currency field for automatic selection
            $table->string('currency', 10)->nullable()->default('INR')->after('bank_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'beneficiary_name',
                'aba_routing_number',
                'us_account_no',
                'account_type',
                'bank_address',
                'currency'
            ]);
        });
    }
};
