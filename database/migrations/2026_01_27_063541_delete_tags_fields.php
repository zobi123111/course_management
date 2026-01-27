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
        Schema::table('user_tag_ratings', function (Blueprint $table) {
               $table->dropColumn([
                'user_id',
                'event_id',
                'tag_expiry_date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_tag_ratings', function (Blueprint $table) {
            $table->Integer('user_id')->nullable(); 
            $table->Integer('event_id')->nullable();
            $table->date('tag_expiry_date')->nullable();
        });
    }
};
