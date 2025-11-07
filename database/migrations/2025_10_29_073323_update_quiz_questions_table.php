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
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('options');
            
            $table->string('option_A')->nullable()->after('question_type');
            $table->string('option_B')->nullable()->after('option_A');
            $table->string('option_C')->nullable()->after('option_B');
            $table->string('option_D')->nullable()->after('option_C');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->json('options');
            
            $table->dropColumn(['option_A', 'option_B', 'option_C', 'option_D']);
        });
    }
};
