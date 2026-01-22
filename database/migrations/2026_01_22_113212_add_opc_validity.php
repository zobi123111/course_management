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
             $table->integer('opc_validity')->nullable()->after('examiner_cbta');
             $table->integer('opc_extend')->nullable()->after('opc_validity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['opc_validity']);
            $table->dropColumn(['opc_extend']);
        });
    }
};
