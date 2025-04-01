<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('organization_units', function (Blueprint $table) {
            $table->string('permission')->nullable()->after('org_logo');
        });
    }

    public function down(): void
    {
        Schema::table('organization_units', function (Blueprint $table) {
            $table->dropColumn('permission');
        });
    }
};
