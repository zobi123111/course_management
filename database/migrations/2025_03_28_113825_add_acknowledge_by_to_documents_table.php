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
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('acknowledged'); // Drop the 'acknowledged' column
            $table->json('acknowledge_by')->nullable()->after('original_filename'); // Add 'acknowledge_by' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('acknowledged')->default(0)->after('original_filename'); // Restore 'acknowledged' column
            $table->dropColumn('acknowledge_by'); // Rollback by dropping 'acknowledge_by'
        });
    }
};

