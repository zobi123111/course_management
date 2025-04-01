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
            $table->string('medical')->nullable()->after('custom_field_value');
            $table->string('medical_adminRequired')->nullable()->after('medical');
            $table->string('medical_issuedby')->nullable()->after('medical_adminRequired');
            $table->string('medical_class')->nullable()->after('medical_issuedby');
            $table->string('medical_issuedate')->nullable()->after('medical_class');
            $table->string('medical_expirydate')->nullable()->after('medical_issuedate');
            $table->string('medical_restriction')->nullable()->after('medical_expirydate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('medical');
            $table->dropColumn('medical_adminRequired');
            $table->dropColumn('medical_issuedby');
            $table->dropColumn('medical_class');
            $table->dropColumn('medical_issuedate');
            $table->dropColumn('medical_expirydate');
            $table->dropColumn('medical_restriction');
        });
    }
};
