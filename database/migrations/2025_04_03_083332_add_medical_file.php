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
        Schema::table('users', function (Blueprint $table) {
            $table->string('medical_file')->nullable()->after('medical_verified');
            $table->string('medical_file_uploaded')->nullable()->after('medical_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('medical_file');
            $table->dropColumn('medical_file_uploaded');
        });
    }
};
