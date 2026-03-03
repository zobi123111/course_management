<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
                $table->integer('event_id')->nullable()->after('ou_id'); 
                $table->integer('course_id')->nullable()->after('event_id');
                $table->integer('lesson_id')->nullable()->after('course_id');  
                $table->integer('trainingEventLesson_id')->nullable()->after('lesson_id'); 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['event_id']);
            $table->dropColumn(['course_id']);
            $table->dropColumn(['lesson_id']);
            $table->dropColumn(['trainingEventLesson_id']);
        });
    }
};
