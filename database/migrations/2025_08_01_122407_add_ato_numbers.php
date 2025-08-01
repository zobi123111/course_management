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
              $table->string('uk_ato_number')->nullable()->after('description');
              $table->string('easa_ato_number')->nullable()->after('uk_ato_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_units', function (Blueprint $table) {
            //
        });
    }
};
