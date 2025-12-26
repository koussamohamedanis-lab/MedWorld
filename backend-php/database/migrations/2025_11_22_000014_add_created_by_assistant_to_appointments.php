<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('appointments', 'created_by_assistant_id')) {
                $table->unsignedBigInteger('created_by_assistant_id')->nullable()->after('consultation_id');
                $table->foreign('created_by_assistant_id')->references('id')->on('assistants')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'created_by_assistant_id')) {
                $table->dropForeign(['created_by_assistant_id']);
                $table->dropColumn('created_by_assistant_id');
            }
        });
    }
};
