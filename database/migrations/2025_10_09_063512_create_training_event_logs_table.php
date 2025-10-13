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
        Schema::create('training_event_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id')->nullable();
            $table->integer('course_id')->nullable();
            $table->integer('lesson_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('is_locked')->nullable()->comment('status = 1 locked, status = 0 unlocked');
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_event_logs');
    }
};
