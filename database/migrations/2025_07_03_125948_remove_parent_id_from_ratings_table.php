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
        Schema::table('ratings', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['parent_id']);

            // Then drop the column
            $table->dropColumn('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Recreate the column
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');

            // Recreate the foreign key constraint
            $table->foreign('parent_id')->references('id')->on('ratings')->onDelete('set null');
        });
    }
};
