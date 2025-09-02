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
        Schema::table('def_lessons', function (Blueprint $table) {
            $table->integer('is_locked')->nullable()->after('lesson_type');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('def_lessons', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });
    }
};
