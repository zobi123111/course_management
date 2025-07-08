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
            $table->date('custom_field_date')->nullable()->after('currency');
            $table->string('custom_field_text')->nullable()->after('custom_field_date');
            $table->boolean('custom_field_admin_verification_required')->default(0)->after('custom_field_text');   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('custom_field_date');
            $table->dropColumn('custom_field_text');
            $table->dropColumn('custom_field_admin_verification_required');
        });
    }
};
