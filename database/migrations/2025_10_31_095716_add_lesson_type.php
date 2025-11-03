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
        Schema::table('training_event_logs', function (Blueprint $table) {
                $table->integer('lesson_type')->default(0)->comment('1 = normal_lesson, 2 = deferred, 3 = custom')->after('is_locked');
             });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_event_logs', function (Blueprint $table) {
              $table->dropColumn('lesson_type');
        });
    }
};
