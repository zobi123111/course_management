<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('topic_id')->nullable();
            $table->text('question_text');
            $table->string('question_type')->default('text');
            $table->string('option_A')->nullable();
            $table->string('option_B')->nullable();
            $table->string('option_C')->nullable();
            $table->string('option_D')->nullable();
            $table->string('correct_option')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_questions');
    }
};

