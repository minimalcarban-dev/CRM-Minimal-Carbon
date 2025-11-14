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
        Schema::table('diamonds', function (Blueprint $table) {
            // Change barcode_image_url to longText to store base64 data URIs
            $table->longText('barcode_image_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamonds', function (Blueprint $table) {
            // Revert to string
            $table->string('barcode_image_url')->nullable()->change();
        });
    }
};
