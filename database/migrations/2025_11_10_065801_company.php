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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Company Details
            $table->string('logo')->nullable(); // Logo file path
            $table->string('gst_no')->nullable(); // GST Number
            $table->string('state_code')->nullable(); // State Code
            $table->string('ein_cin_no')->nullable(); // EIN/CIN Number
            
            // Address & Location
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            
            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('ad_code')->nullable();
            $table->string('sort_code')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('iban')->nullable();
            $table->string('account_holder_name')->nullable();
            
            // Status
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
