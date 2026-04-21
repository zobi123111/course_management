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
        Schema::table('bookings', function (Blueprint $table) {
             $table->integer('bookingCreatedRole_id')->nullable()->after('send_email');
            $table->integer('bookingCreated_by')->nullable()->after('bookingCreatedRole_id');
            $table->integer('approver_id')->nullable()->after('bookingCreated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('bookingCreatedRole_id');
            $table->dropColumn('bookingCreated_by');
            $table->dropColumn('approver_id');
        });
    }
};
