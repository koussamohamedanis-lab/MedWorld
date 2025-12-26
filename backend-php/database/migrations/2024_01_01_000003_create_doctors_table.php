<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('speciality')->nullable();
            $table->string('licenseNumber')->nullable();
            $table->timestamp('careerStart')->nullable();
            $table->decimal('consultationPrice', 10, 2)->default(0);
            $table->integer('consultationDuration')->default(30);
            $table->unsignedBigInteger('cabinetId')->nullable();
            $table->timestamps();

            $table->unique('userId');
            $table->index('cabinetId');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cabinetId')->references('id')->on('cabinets')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctors');
    }
};
