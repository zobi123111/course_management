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
            $table->string('kind_of_rating')->nullable()->after('name');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropColumn('kind_of_rating');
        });
    }
};
