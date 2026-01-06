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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('category')->default('general'); // greeting, follow_up, closing, catalog, pricing
            $table->text('content');

            // Variables that can be substituted: {{name}}, {{date}}, etc.
            $table->json('variables')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();

            // Usage tracking
            $table->integer('usage_count')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
