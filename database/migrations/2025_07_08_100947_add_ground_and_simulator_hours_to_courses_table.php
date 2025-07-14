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
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('enable_groundschool_time')->default(0)->after('duration_value');
            $table->decimal('groundschool_hours', 5, 2)->nullable()->after('enable_groundschool_time');

            $table->boolean('enable_simulator_time')->default(0)->after('groundschool_hours');
            $table->decimal('simulator_hours', 5, 2)->nullable()->after('enable_simulator_time');
            $table->boolean('enable_custom_time_tracking')->default(0)->after('simulator_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn([
                'enable_groundschool_time',
                'groundschool_hours',
                'enable_simulator_time',
                'simulator_hours'
            ]);
        });
    }
};
