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
        Schema::table('courses', function (Blueprint $table) {
             // Ensure the column exists and is the correct data type
             $table->unsignedBigInteger('ou_id')->change();
            
             // Add the foreign key constraint
             $table->foreign('ou_id')->references('id')->on('organization_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['ou_id']); // Drop foreign key

        });
    }
};
