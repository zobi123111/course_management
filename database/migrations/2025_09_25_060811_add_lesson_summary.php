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
        Schema::table('task_gradings', function (Blueprint $table) {
             $table->longText('lesson_summary')->nullable()->after('created_by');
             $table->longText('instructor_comment')->nullable()->after('lesson_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_gradings', function (Blueprint $table) {
            $table->dropColumn('lesson_summary');
            $table->dropColumn('instructor_comment');
        });
    }
};
