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
        Schema::table('user_opc_ratings', function (Blueprint $table) {
            $table->dropUnique('user_opc_ratings_user_id_aircraft_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_opc_ratings', function (Blueprint $table) {
            $table->unique(['user_id', 'aircraft_type']);
        });
    }
};
