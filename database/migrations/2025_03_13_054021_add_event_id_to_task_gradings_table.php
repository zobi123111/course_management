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
        Schema::table('task_gradings', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->foreign('event_id')->references('id')->on('training_events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_gradings', function (Blueprint $table) {
            $table->dropForeign(['event_id']); // Drop foreign key constraint
            $table->dropColumn('event_id');    // Drop the column itself
        });
    }
};
