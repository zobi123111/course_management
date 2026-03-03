<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->time('takeoff_time')->nullable();
            $table->time('landing_time')->nullable();
            $table->time('tacho_start_time')->nullable();
            $table->time('tacho_stop_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'takeoff_time',
                'landing_time',
                'tacho_start_time',
                'tacho_stop_time'
            ]);
        });
    }
};
