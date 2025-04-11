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
        Schema::table('task_gradings', function (Blueprint $table) {
            $table->text('task_comment')->nullable()->after('task_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_gradings', function (Blueprint $table) {
            $table->dropColumn('task_comment');
        });
    }
};
