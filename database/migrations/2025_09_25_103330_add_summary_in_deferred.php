<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('def_lessons', function (Blueprint $table) {
             $table->longText('lesson_summary')->nullable()->after('is_locked');
             $table->longText('instructor_comment')->nullable()->after('lesson_summary');
        });
    }

    public function down(): void
    {
        Schema::table('def_lessons', function (Blueprint $table) {
            $table->dropColumn('lesson_summary');
            $table->dropColumn('instructor_comment');
        });
    }
};
