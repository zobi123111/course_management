<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->integer('quiz_id');
            $table->integer('user_id');
            $table->foreignId('question_id')->constrained('quiz_questions')->onDelete('cascade');
            $table->text('selected_option')->nullable();
            $table->boolean('is_correct')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_answers');
    }
};
