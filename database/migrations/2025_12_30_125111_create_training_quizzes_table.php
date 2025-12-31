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
        Schema::create('training_quizzes', function (Blueprint $table) {
            $table->id();
            $table->integer('trainingevent_id');
            $table->integer('student_id');
            $table->integer('quiz_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('training_quizzes');
    }
};
