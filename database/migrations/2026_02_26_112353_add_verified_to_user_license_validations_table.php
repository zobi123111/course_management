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
        Schema::table('user_license_validations', function (Blueprint $table) {
            $table->boolean('verified')->default(false)->after('admin_verification_required');
        });
    }

    public function down(): void
    {
        Schema::table('user_license_validations', function (Blueprint $table) {
            $table->dropColumn('verified');
        });
    }
};
