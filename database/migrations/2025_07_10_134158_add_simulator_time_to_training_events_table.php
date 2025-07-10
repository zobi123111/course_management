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
            $table->decimal('simulator_time', 5, 2)->nullable()->after('total_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_events', function (Blueprint $table) {
            $table->dropColumn('simulator_time');
        });
    }
};
