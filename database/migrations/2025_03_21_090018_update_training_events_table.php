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
             $table->unsignedBigInteger('student_id')->nullable()->after('group_id');
             // Adding foreign key columns
             $table->unsignedBigInteger('resource_id')->nullable()->after('instructor_id');
             $table->foreign('resource_id')->references('id')->on('resources')->onDelete('cascade');
     
             // Adding new columns
             $table->date('event_date')->nullable()->after('resource_id');
             $table->string('departure_airfield', 4)->nullable()->after('end_time'); // 4-letter text
             $table->string('destination_airfield', 4)->nullable()->after('departure_airfield'); // 4-letter text
             $table->time('total_time')->nullable()->after('destination_airfield'); // To be calculated
             $table->string('licence_number')->nullable()->after('total_time'); // From user profile
         });
     }
     
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::table('training_events', function (Blueprint $table) {
             // Drop foreign keys first
             $table->dropForeign(['resource_id']);
     
             // Drop columns
             $table->dropColumn([
                 'student_id',
                 'resource_id',
                 'event_date',
                 'departure_airfield',
                 'destination_airfield',
                 'total_time',
                 'licence_number'
             ]);
         });
     }
     
};
