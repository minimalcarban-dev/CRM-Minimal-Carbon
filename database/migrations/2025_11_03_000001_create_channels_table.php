<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('group');
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('admins');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('channel_user', function (Blueprint $table) {
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->json('settings')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->primary(['channel_id', 'admin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_user');
        Schema::dropIfExists('channels');
    }
};