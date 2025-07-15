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
        Schema::table('def_lessons', function (Blueprint $table) {
            $table->string('departure_airfield', 4)->nullable()->after('end_time');
            $table->string('destination_airfield', 4)->nullable()->after('departure_airfield');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('def_lessons', function (Blueprint $table) {
            $table->dropColumn('departure_airfield');
            $table->dropColumn('destination_airfield');
        });
    }
};
