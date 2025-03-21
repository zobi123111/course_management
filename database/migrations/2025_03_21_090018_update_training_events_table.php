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
        Schema::table('training_events', function (Blueprint $table) {
            // Modifying existing column (if needed)
            $table->dateTime('start_time')->nullable()->change();  // Ensure it includes date + time
            $table->dateTime('end_time')->nullable()->change();    // Ensure it includes date + time

            // Adding new columns
            $table->string('departure_airfield', 4)->nullable()->after('end_time'); // 4-letter text
            $table->string('destination_airfield', 4)->nullable()->after('departure_airfield'); // 4-letter text
            $table->unsignedBigInteger('resource_id')->nullable()->after('destination_airfield'); // Resource from another table
            $table->time('total_time')->nullable()->after('resource_id'); // To be calculated
            $table->string('licence_number')->nullable()->after('total_time'); // From user profile

            // Foreign key for resource selection
            $table->foreign('resource_id')->references('id')->on('resources')->onDelete('set null');
            $table->unsignedBigInteger('student_id')->nullable()->after('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_events', function (Blueprint $table) {
            $table->dropColumn([
                'student_id',
                'departure_airfield', 
                'destination_airfield', 
                'resource_id', 
                'total_time', 
                'licence_number'
            ]);
        });
    }
};
