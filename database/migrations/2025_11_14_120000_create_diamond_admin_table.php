<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diamond_admin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diamond_id');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('assign_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->foreign('diamond_id')->references('id')->on('diamonds')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['diamond_id', 'admin_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diamond_admin');
    }
};
