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
        Schema::table('user_ratings', function (Blueprint $table) {
            $table->enum('linked_to', ['licence_1', 'licence_2', 'general'])->default('general')->after('rating_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_ratings', function (Blueprint $table) {
            $table->dropColumn('linked_to');
        });
    }
};
