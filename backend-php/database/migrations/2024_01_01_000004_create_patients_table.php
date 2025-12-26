<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('emergencyContact')->nullable();
            $table->string('bloodType')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->text('medicalHistory')->nullable();
            $table->text('allergies')->nullable();
            $table->timestamps();

            $table->unique('userId');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
};
