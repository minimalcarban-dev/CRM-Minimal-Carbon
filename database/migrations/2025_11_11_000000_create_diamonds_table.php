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
        Schema::create('diamonds', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('stockid')->unique();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('listing_price', 10, 2)->nullable();
            $table->string('cut')->nullable();
            $table->string('shape')->nullable();
            $table->string('measurement')->nullable();
            $table->integer('number_of_pics')->default(0);
            $table->string('barcode_number')->unique();
            $table->string('barcode_image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diamonds');
    }
};
