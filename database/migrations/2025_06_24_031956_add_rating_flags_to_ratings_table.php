<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('ratings', function (Blueprint $table) {
        $table->boolean('is_fixed_wing')->default(false);
        $table->boolean('is_rotary')->default(false);
        $table->boolean('is_instructor')->default(false);
        $table->boolean('is_examiner')->default(false);
    });
}

    /**
     * Reverse the migrations.
     */
   public function down()
{
    Schema::table('ratings', function (Blueprint $table) {
        $table->dropColumn(['is_fixed_wing', 'is_rotary', 'is_instructor', 'is_examiner']);
    });
}
};
