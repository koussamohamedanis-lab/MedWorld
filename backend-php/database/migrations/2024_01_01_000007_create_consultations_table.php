<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctorId');
            $table->unsignedBigInteger('patientId');
            $table->unsignedBigInteger('appointmentId')->nullable();
            $table->text('notes')->nullable();
            $table->text('prescriptions')->nullable();
            $table->text('attachments')->nullable();
            $table->timestamps();

            $table->index('doctorId');
            $table->index('patientId');
            $table->index('appointmentId');
            $table->foreign('doctorId')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('patientId')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('appointmentId')->references('id')->on('appointments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultations');
    }
};
