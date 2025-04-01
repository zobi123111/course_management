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
        Schema::create('course_template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('course_templates')->onDelete('cascade');
            $table->string('field_name')->nullable(); // Custom field name
            $table->string('grading_type')->nullable(); // Grading type
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_template_fields');
    }
};
