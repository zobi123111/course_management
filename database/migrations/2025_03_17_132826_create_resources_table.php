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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->integer('registration')->nullable();
            $table->string('type')->nullable();
            $table->string('class')->nullable();
            $table->string('note')->nullable();
            $table->integer('hours_from_rts')->nullable();
            $table->date('date_from_rts')->nullable();
            $table->date('date_for_maintenance')->nullable();
            $table->integer('hours_remaining')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
