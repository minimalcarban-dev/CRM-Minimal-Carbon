<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('email_accounts')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable(); // Action performer
            $table->string('action'); // e.g., 'oauth_connect', 'sync_start', 'email_deleted', 'revoke_access'
            $table->string('entity_type')->nullable(); // e.g., 'Email', 'EmailAccount'
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable(); // Request details, previous state vs new state
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_audit_logs');
    }
};
