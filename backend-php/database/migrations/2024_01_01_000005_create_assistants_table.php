<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('assistants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('doctorId')->nullable();
            $table->unsignedBigInteger('cabinetId')->nullable();
            $table->timestamps();

            $table->unique('userId');
            $table->index('doctorId');
            $table->index('cabinetId');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctorId')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('cabinetId')->references('id')->on('cabinets')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('assistants');
    }
};
