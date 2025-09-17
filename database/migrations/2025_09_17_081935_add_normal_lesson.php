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
        Schema::table('sub_lessons', function (Blueprint $table) {
             $table->integer('normal_lesson')->default(0)->after('position');
             $table->integer('event_id')->nullable()->after('normal_lesson');
             $table->integer('user_id')->nullable()->after('event_id');
             $table->integer('task_id')->nullable()->after('user_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_lessons', function (Blueprint $table) {
            $table->dropColumn('normal_lesson');
            $table->dropColumn('event_id');
            $table->dropColumn('user_id');
            $table->dropColumn('task_id');
        });
    }
};
