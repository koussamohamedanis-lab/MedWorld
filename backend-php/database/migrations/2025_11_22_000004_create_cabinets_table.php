<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cabinets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('image')->nullable();
            $table->boolean('access_handicap')->default(false);
            $table->boolean('has_parking')->default(false);
            $table->boolean('has_wifi')->default(false);
            $table->boolean('accepts_urgent')->default(false);
            $table->boolean('accepts_insurance')->default(false);
            $table->json('opening_hours')->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabinets');
    }
};
