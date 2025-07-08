<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lesson_prerequisite_details', function (Blueprint $table) {
            $table->unsignedInteger('prereq_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_prerequisite_details', function (Blueprint $table) {
            $table->dropColumn('prereq_id');
        });
    }
};
