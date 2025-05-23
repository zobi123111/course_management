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
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('ou_id')->nullable()->after('id');
            $table->foreign('ou_id')->references('id')->on('organization_units')->onDelete('cascade');
            $table->unsignedBigInteger('group_id')->nullable()->after('folder_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['ou_id']); // Drop foreign key constraint
            $table->dropColumn('ou_id');    // Drop the column
            $table->dropForeign(['group_id']); // Drop foreign key constraint
            $table->dropColumn('group_id');    // Drop the column
        });
    }
};
