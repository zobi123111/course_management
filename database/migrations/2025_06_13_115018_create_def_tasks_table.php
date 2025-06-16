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
        Schema::create('def_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // student_id
            $table->unsignedBigInteger('task_id')->nullable(); // corresponds to sub_lesson_id or task_grading_id
            $table->unsignedBigInteger('created_by')->nullable(); // instructor/admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('def_tasks');
    }
};
