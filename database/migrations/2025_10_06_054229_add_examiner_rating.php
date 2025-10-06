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
        Schema::table('course_lessons', function (Blueprint $table) {
              $table->integer('instructor_cbta')->default(0)->after('enable_cbta');
              $table->integer('examiner_cbta')->default(0)->after('instructor_cbta');
        });
    }

 
    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn('instructor_cbta');
            $table->dropColumn('examiner_cbta');
        });
    }
};
