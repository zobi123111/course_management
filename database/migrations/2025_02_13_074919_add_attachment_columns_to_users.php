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
            $table->string('licence')->nullable();
            $table->string('licence_file')->nullable();
            $table->string('passport')->nullable();
            $table->string('passport_file')->nullable();
            $table->string('rating')->nullable();
            $table->string('currency')->nullable();
            $table->string('custom_field_name')->nullable();
            $table->text('custom_field_value')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'licence', 
                'licence_file',
                'passport',
                'passport_file',
                'rating',
                'currency',
                'custom_field_name',
                'custom_field_value',
            ]);
        });
    }

};
