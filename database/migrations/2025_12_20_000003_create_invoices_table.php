<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->date('invoice_date');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->enum('invoice_type', ['proforma', 'tax'])->default('tax');
            $table->string('place_of_supply')->nullable();
            $table->string('payment_terms')->nullable();
            $table->foreignId('billed_to_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('shipped_to_id')->nullable()->constrained('parties')->nullOnDelete();

            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('total_invoice_value', 15, 2)->default(0);

            $table->enum('status', ['draft', 'final', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
