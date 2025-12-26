<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctorId');
            $table->unsignedBigInteger('cabinetId')->nullable();
            $table->text('availability')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('doctorId');
            $table->foreign('doctorId')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('cabinetId')->references('id')->on('cabinets')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('calendars');
    }
};
