<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phoneNumber')->nullable();
            $table->string('avatarUrl')->nullable();
            $table->text('address')->nullable();
            $table->string('gender')->nullable();
            $table->timestamp('dateOfBirth')->nullable();
            $table->enum('type', ['superadmin', 'admin', 'doctor', 'assistant', 'patient']);
            $table->timestamps();
            $table->softDeletes();
            $table->rememberToken();

            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
