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
        Schema::table('ou_settings', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('teachtrack_email_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ou_settings', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
