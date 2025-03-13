<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Licence Fields
            $table->boolean('licence_admin_verification_required')->default(false)->after('licence_file');
            $table->boolean('licence_verified')->default(false)->after('licence_admin_verification_required'); 
            $table->date('licence_expiry_date')->nullable()->after('licence_verified');
            $table->boolean('licence_non_expiring')->default(false)->after('licence_expiry_date'); 

            // Passport Fields
            $table->boolean('passport_admin_verification_required')->default(false)->after('passport_file');
            $table->boolean('passport_verified')->default(false)->after('passport_admin_verification_required'); 
            $table->date('passport_expiry_date')->nullable()->after('passport_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'licence_admin_verification_required',
                'licence_verified',
                'licence_expiry_date',
                'licence_non_expiring',
                'passport_admin_verification_required',
                'passport_verified',
                'passport_expiry_date'
            ]);
        });
    }
};
