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
        Schema::create('user_opc_ratings', function (Blueprint $table) {
            $table->id();
            $table->Integer('user_id');
            $table->Integer('event_id');
            $table->Integer('course_id');
            $table->Integer('aircraft_type');
            $table->date('opc_expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'aircraft_type']);
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('user_opc_ratings');
    }

};
