<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('prescription_date');
            $table->enum('status', ['ACTIVE', 'COMPLETED', 'CANCELLED'])->default('ACTIVE');
            $table->json('medications')->nullable();
            $table->text('general_instructions')->nullable();
            $table->date('valid_until')->nullable();
            $table->integer('refills_allowed')->default(0);
            $table->integer('refills_used')->default(0);
            $table->timestamps();

            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
