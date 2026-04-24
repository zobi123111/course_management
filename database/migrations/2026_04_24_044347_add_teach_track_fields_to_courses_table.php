<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('teach_track')->nullable();
            $table->boolean('is_instructor')->default(false);
            $table->boolean('is_examiner')->default(false);
            $table->string('training_type')->nullable();
            $table->date('validity')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'teach_track',
                'is_instructor',
                'is_examiner',
                'training_type',
                'validity'
            ]);
        });
    }
};