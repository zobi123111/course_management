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
        Schema::create('examiner_gradings', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->nullable();
            $table->string('cbta_gradings_id')->nullable();
            $table->string('competency_value')->nullable();
            $table->string('user_id')->nullable();
            $table->string('competency_type')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examiner_gradings');
    }
};
