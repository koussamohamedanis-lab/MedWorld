<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date');
            $table->enum('status', ['SCHEDULED', 'CONFIRMED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED', 'NO_SHOW'])->default('SCHEDULED');
            $table->unsignedBigInteger('patientId');
            $table->unsignedBigInteger('doctorId');
            $table->unsignedBigInteger('cabinetId');
            $table->timestamps();

            $table->index('patientId');
            $table->index('doctorId');
            $table->index('cabinetId');
            $table->foreign('patientId')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctorId')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('cabinetId')->references('id')->on('cabinets')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
