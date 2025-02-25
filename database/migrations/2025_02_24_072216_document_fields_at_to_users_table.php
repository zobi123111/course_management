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
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('is_admin')->nullable();
            $table->bigInteger('licence_required')->nullable()->after('deleted_at');
            $table->bigInteger('passport_required')->nullable()->after('licence_file');
            $table->bigInteger('rating_required')->nullable()->after('passport_file');
            $table->bigInteger('currency_required')->nullable()->after('rating');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_admin',
                'licence_required', 
                'passport_required',
                'rating_required',
                'currency_required',
            ]);
        });
    }
};
