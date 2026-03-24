<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('allowed_ips', function (Blueprint $table) {
            $table->string('device_token', 128)->nullable()->unique()->after('ip_address');
            $table->string('user_agent', 500)->nullable()->after('device_token');
            $table->timestamp('last_used_at')->nullable()->after('user_agent');
            $table->string('city', 100)->nullable()->after('last_used_at');
            $table->string('country', 100)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('allowed_ips', function (Blueprint $table) {
            $table->dropUnique(['device_token']);
            $table->dropColumn(['device_token', 'user_agent', 'last_used_at', 'city', 'country']);
        });
    }
};
