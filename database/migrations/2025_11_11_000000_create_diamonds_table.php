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
            $table->string('lot_no')->unique();
            $table->string('sku')->unique();
            
            // Specifications
            $table->string('material')->nullable();
            $table->string('cut')->nullable();
            $table->string('clarity')->nullable();
            $table->string('color')->nullable();
            $table->string('shape')->nullable();
            $table->string('measurement')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('per_ct', 10, 2)->nullable();
            
            // Pricing
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('margin', 10, 2)->nullable();
            $table->decimal('listing_price', 10, 2)->nullable();
            $table->decimal('shipping_price', 10, 2)->default(0);
            
            // Lifecycle & Status
            $table->date('purchase_date')->nullable();
            $table->date('sold_out_date')->nullable();
            $table->string('is_sold_out')->default('IN Stock');
            $table->integer('duration_days')->default(0);
            $table->decimal('duration_price', 15, 2)->default(0);
            $table->decimal('sold_out_price', 15, 2)->nullable();
            $table->decimal('profit', 10, 2)->nullable();
            $table->string('sold_out_month', 7)->nullable();
            
            // Barcode
            $table->string('barcode_number')->unique();
            $table->string('barcode_image_url')->nullable();
            
            // Additional Details
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->string('diamond_type')->nullable();
            $table->json('multi_img_upload')->nullable();
            
            // Admin Assignment
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('assign_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            
            // Foreign keys
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('assign_by')->references('id')->on('admins')->onDelete('set null');
            
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
