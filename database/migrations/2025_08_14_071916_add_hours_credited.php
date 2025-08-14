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
        Schema::table('def_lesson_tasks', function (Blueprint $table) {
             $table->string('hours_credited')->nullable()->after('task_id');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('def_lesson_tasks', function (Blueprint $table) {
            $table->dropColumn('hours_credited');
        });
    }
};
