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
        Schema::create('def_lesson_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('def_lesson_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // student_id
            $table->unsignedBigInteger('task_id')->nullable(); // sub_lesson_id or task_grading_id
            $table->string('task_grade')->nullable();
            $table->text('task_comment')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('def_lesson_tasks');
    }
};
