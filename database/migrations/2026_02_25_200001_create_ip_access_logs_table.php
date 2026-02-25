<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ip_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->default('GET');
            $table->string('user_agent', 500)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('isp', 200)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamps();

            $table->index('ip_address');
            $table->index('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_access_logs');
    }
};
