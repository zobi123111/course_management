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
        Schema::create('validate_training_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id');
            $table->integer('course_id');
            $table->integer('tag_id');
            $table->integer('validate_status')->comment('0 = not validated, 1 = validated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validate_training_tags');
    }
};
