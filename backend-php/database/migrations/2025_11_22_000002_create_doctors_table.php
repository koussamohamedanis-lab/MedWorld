<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('speciality')->nullable();
            $table->date('career_start')->nullable();
            $table->decimal('consultation_price', 8, 2)->default(0);
            $table->integer('consultation_duration')->default(30);
            $table->unsignedBigInteger('cabinet_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cabinet_id')->references('id')->on('cabinets')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
