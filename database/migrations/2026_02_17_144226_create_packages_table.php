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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('slip_id')->unique();
            $table->string('person_name');
            $table->string('mobile_number');
            $table->text('package_description');
            $table->string('package_image')->nullable(); // Cloudinary URL

            $table->date('issue_date');
            $table->time('issue_time');

            $table->date('return_date'); // Expected Return Date

            $table->date('actual_return_date')->nullable();
            $table->time('actual_return_time')->nullable();

            $table->string('status')->default('Issued'); // Issued, Returned, Overdue
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('admin_id')->nullable();
            // Assuming 'admins' table is used for auth guard 'admin'. verify this or use 'users' if unified.
            // Based on routes/web.php, auth guard is 'admin', model might be Admin or User. 
            // composer.json does not show spatie/laravel-permission but route middleware suggests custom permission.
            // Will make it nullable for now or check Admin model existence.

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
