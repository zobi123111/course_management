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
        Schema::create('training_event_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_event_id')->nullable();
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->integer('resource_id')->nullable();
            $table->date('lesson_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Foreign keys
            // $table->foreign('training_event_id')->references('id')->on('training_events')->onDelete('cascade');
            // $table->foreign('lesson_id')->references('id')->on('course_lessons')->onDelete('cascade');
            // $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_event_lessons');
    }
};
