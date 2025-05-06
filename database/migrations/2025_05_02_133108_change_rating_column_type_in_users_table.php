<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('rating')->nullable()->change(); // Allow NULL values
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rating')->change(); // Revert back to string if needed
        });
    }
};
