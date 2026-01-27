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
        Schema::create('training_tags', function (Blueprint $table) {
            $table->id();
            $table->Integer('user_id')->nullable(); 
            $table->Integer('event_id')->nullable();
            $table->Integer('course_id')->nullable();
            $table->Integer('tag_id')->nullable();
            $table->Integer('aircraft_type')->nullable();
            $table->date('tag_expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_tags');
    }
};
