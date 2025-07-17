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
            $table->decimal('custom_hours_credited',5,2)->nullable()->after('hours_credited');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->dropColumn('custom_hours_credited');
        });
    }
};
