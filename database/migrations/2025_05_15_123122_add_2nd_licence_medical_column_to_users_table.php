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
            $table->boolean('licence_2_required')->default(0)->after('licence_non_expiring');
            $table->boolean('licence_2_admin_verification_required')->default(0)->after('licence_2_required');
            $table->boolean('medical_2_required')->default(0)->after('medical_file_uploaded');
            $table->boolean('medical_2_adminRequired')->default(0)->after('medical_2_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('licence_2_required');
            $table->dropColumn('licence_2_admin_verification_required');
            $table->dropColumn('medical_2_required');
            $table->dropColumn('medical_2_adminRequired');
        });
    }
};
