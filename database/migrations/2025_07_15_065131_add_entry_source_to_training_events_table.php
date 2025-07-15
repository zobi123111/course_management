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
            $table->string('entry_source')->nullable()->after('ou_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('training_events', function (Blueprint $table) {
            $table->dropColumn('entry_source');
        });
    }
};
