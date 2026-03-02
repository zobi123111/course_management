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
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->decimal('tacho_start_time', 5, 2)
                  ->nullable()
                  ->change();

            $table->decimal('tacho_stop_time', 5, 2)
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->time('tacho_start_time')
                  ->nullable()
                  ->change();

            $table->time('tacho_stop_time')
                  ->nullable()
                  ->change();
        });
    }
};
