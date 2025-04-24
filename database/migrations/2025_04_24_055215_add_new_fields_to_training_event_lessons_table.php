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
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->string('departure_airfield', 4)->nullable()->after('end_time'); // 4-letter text
            $table->string('destination_airfield', 4)->nullable()->after('departure_airfield'); // 4-letter text
            $table->string('instructor_license_number')->nullable()->after('destination_airfield'); // From user profile                
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'departure_airfield',
                'destination_airfield',
                'instructor_license_number'
            ]);
        });
    }
};
