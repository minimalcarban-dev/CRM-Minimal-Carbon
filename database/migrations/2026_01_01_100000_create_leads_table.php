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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Platform Information
            $table->enum('platform', ['facebook', 'instagram'])->default('facebook');
            $table->string('platform_user_id')->index();
            $table->string('username')->nullable();
            $table->string('profile_pic_url')->nullable();

            // Status & Priority
            $table->enum('status', ['new', 'in_process', 'completed', 'lost'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');

            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();

            // Timestamps for tracking
            $table->timestamp('first_contact_at')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('sla_deadline')->nullable();

            // Lead Scoring
            $table->integer('lead_score')->default(0);

            // Additional Data
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();

            // Timestamps & Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('priority');
            $table->index('lead_score');
            $table->index('sla_deadline');
            $table->index(['platform', 'platform_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
