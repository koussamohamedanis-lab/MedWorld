<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('adminId')->nullable();
            $table->text('location')->nullable();
            $table->text('openingHours')->nullable();
            $table->boolean('accessHandicap')->nullable();
            $table->boolean('hasParking')->nullable();
            $table->boolean('hasWifi')->nullable();
            $table->boolean('acceptsUrgent')->nullable();
            $table->boolean('acceptsInsurance')->nullable();
            $table->timestamps();

            $table->index('adminId');
            $table->foreign('adminId')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cabinets');
    }
};
