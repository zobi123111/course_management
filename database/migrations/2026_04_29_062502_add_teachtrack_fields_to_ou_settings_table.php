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

            $table->boolean('teachtrack_enabled')
                ->default(false)
                ->after('timezone');

            $table->integer('teachtrack_validity_months')
                ->default(12)
                ->after('teachtrack_enabled');

            $table->integer('teachtrack_alert_days')
                ->default(30)
                ->after('teachtrack_validity_months');

            $table->boolean('teachtrack_email_enabled')
                ->default(false)
                ->after('teachtrack_alert_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ou_settings', function (Blueprint $table) {

            $table->dropColumn([
                'teachtrack_enabled',
                'teachtrack_validity_months',
                'teachtrack_alert_days',
                'teachtrack_email_enabled'
            ]);
        });
    }
};
