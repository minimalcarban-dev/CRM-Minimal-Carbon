<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('allowed_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45); // Supports IPv4 & IPv6
            $table->string('label', 255)->nullable(); // Friendly name like "Office WiFi"
            $table->boolean('is_active')->default(true);
            $table->foreignId('added_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->unique('ip_address');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowed_ips');
    }
};
