<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('state')->nullable();
            $table->string('state_code')->nullable();
            $table->string('country')->nullable();
            $table->string('tax_id')->nullable();
            $table->boolean('is_foreign')->default(false);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parties');
    }
};
