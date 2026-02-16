<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_status')->nullable()->after('tracking_url');
            $table->json('tracking_history')->nullable()->after('tracking_status');
            $table->timestamp('last_tracker_sync')->nullable()->after('tracking_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_status', 'tracking_history', 'last_tracker_sync']);
        });
    }
};
