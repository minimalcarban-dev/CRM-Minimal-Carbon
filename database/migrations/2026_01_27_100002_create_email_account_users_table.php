<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_account_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('email_accounts')->onDelete('cascade');
            $table->unsignedBigInteger('user_id'); // Link to admins table
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->enum('role', ['owner', 'manager', 'agent', 'auditor'])->default('agent');
            $table->timestamps();

            $table->unique(['email_account_id', 'user_id', 'company_id'], 'acc_user_comp_unique');
            $table->foreign('user_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_account_users');
    }
};
