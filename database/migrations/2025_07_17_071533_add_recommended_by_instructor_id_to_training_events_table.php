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
        Schema::table('training_events', function (Blueprint $table) {
            $table->integer('recommended_by_instructor_id')->nullable()->after('course_end_date');
        });
    }

    /**
     * Reverse the migrations.
    */

    public function down(): void
    {
        Schema::table('training_events', function (Blueprint $table) {
            $table->dropColumn('recommended_by_instructor_id');
        });
    }
};
