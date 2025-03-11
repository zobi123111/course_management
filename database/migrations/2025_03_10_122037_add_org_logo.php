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
        Schema::table('organization_units', function (Blueprint $table) {
            $table->string('org_logo')->nullable()->after('status');
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_units', function (Blueprint $table) {
            $table->dropColumn('org_logo'); 
        });
    }
};
