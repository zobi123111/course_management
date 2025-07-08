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
            $table->boolean('is_locked')->default(false)->after('std_license_number'); // Locked after grading
            $table->boolean('student_acknowledged')->default(false)->after('is_locked'); // Student has acknowledged
            $table->text('student_acknowledgement_comments')->nullable()->after('student_acknowledged'); // Optional comments
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_events', function (Blueprint $table) {
            $table->dropColumn('is_locked');
            $table->dropColumn('student_acknowledged');
            $table->dropColumn('student_acknowledgement_comments');
        });
    }
};
