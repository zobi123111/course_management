<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('quiz_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizs')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');  // Assuming 'users' table
            $table->timestamp('assigned_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_assignments');
    }
};
