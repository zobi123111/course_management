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
        Schema::create('course_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ou_id')->constrained('organization_units')->onDelete('cascade');
            $table->string('name')->nullable(); // Template Namephp 
            $table->text('description')->nullable();
            $table->boolean('enable_cbta')->default(false); // Checkbox for CBTA grading
            $table->boolean('enable_manual_time_entry')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_templates');
    }
};
