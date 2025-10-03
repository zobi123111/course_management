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
        Schema::create('cbta_gradings', function (Blueprint $table) {
            $table->id();
            $table->string('competency')->nullable();
            $table->string('short_name')->nullable();
            $table->string('competency_type')->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('cbta_gradings');
    }
};
