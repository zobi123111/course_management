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
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->integer('operation1')->nullable()->after('instructor_comment');
            $table->integer('role1')->nullable()->after('operation1');

            $table->integer('operation2')->nullable()->after('role1');
            $table->integer('role2')->nullable()->after('operation2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_event_lessons', function (Blueprint $table) {
            $table->dropColumn('operation1');
            $table->dropColumn('role1');
            $table->dropColumn('operation2');
            $table->dropColumn('role2');
        });
    }
};
