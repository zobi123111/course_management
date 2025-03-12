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
        Schema::create('course_prerequisite_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('prerequisite_type');
            $table->text('prerequisite_detail')->nullable();
            $table->string('file_path')->nullable(); // For file uploads
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Tracking who added it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_prerequisite_details');
    }
};
