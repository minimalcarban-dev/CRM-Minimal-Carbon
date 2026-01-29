<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('emails')->onDelete('cascade');
            $table->string('attachment_id'); // Gmail specific attachment ID
            $table->string('filename');
            $table->string('content_type');
            $table->integer('size_bytes');
            $table->string('storage_path')->nullable(); // If stored locally/S3
            $table->boolean('is_inline')->default(false);
            $table->string('content_id')->nullable();
            $table->timestamps();

            $table->index(['email_id', 'attachment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
    }
};
