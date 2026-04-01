<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_sectors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->date('lesson_date')->nullable();
            $table->string('departure_airfield')->nullable();
            $table->string('destination_airfield')->nullable();
            $table->time('start_time')->nullable();
            $table->time('takeoff_time')->nullable();
            $table->time('landing_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_sectors');
    }
};