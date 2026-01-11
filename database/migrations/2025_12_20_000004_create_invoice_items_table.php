<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->text('description_of_goods')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedInteger('pieces')->nullable();
            $table->decimal('carats', 12, 3)->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
};
