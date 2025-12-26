<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assistant_doctor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assistant_id');
            $table->unsignedBigInteger('doctor_id');
            $table->timestamps();

            $table->foreign('assistant_id')->references('id')->on('assistants')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->unique(['assistant_id', 'doctor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_doctor');
    }
};
