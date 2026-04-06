<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('lesson_sectors', function (Blueprint $table) {
            $table->string('lesson_type')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('lesson_sectors', function (Blueprint $table) {
            $table->tinyInteger('lesson_type')->nullable()->change();
        });
    }
};
