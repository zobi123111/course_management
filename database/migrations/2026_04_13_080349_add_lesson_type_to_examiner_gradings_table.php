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
        Schema::table('examiner_gradings', function (Blueprint $table) {
            $table->string('lesson_type')->nullable()->after('competency_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examiner_gradings', function (Blueprint $table) {
            $table->dropColumn('lesson_type');
        });
    }
};
