<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ip_access_requests', function (Blueprint $table) {
            $table->string('request_token', 128)->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('ip_access_requests', function (Blueprint $table) {
            $table->dropColumn('request_token');
        });
    }
};
