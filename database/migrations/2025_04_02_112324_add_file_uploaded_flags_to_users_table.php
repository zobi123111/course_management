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
            $table->string('licence_file_uploaded')->nullable()->after('licence_expiry_date');
            $table->string('passport_file_uploaded')->nullable()->after('passport_expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('licence_file_uploaded');
            $table->dropColumn('passport_file_uploaded');
        });
    }
};
