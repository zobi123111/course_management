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
        Schema::table('quizs', function (Blueprint $table) {
            $table->enum('question_selection', ['manual', 'random'])->default('manual')->after('show_result');

            $table->unsignedInteger('question_count')->nullable()->after('question_selection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizs', function (Blueprint $table) {
            $table->dropColumn(['question_selection', 'question_count']);
        });
    }
};
