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
        Schema::create('overall_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('training_events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('result')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by'); // Added column without foreign key constraint
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overall_assessment');
    }
};
