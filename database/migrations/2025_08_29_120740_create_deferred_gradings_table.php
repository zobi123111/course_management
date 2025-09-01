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
        Schema::create('deferred_gradings', function (Blueprint $table) {
               $table->id();
            $table->integer('event_id')->nullable();
            $table->integer('deflesson_id')->nullable();
            $table->integer('user_id')->nullable();

            $table->tinyInteger('kno_grade')->nullable();
            $table->text('kno_comment')->nullable();

            $table->tinyInteger('pro_grade')->nullable();
            $table->text('pro_comment')->nullable();

            $table->tinyInteger('com_grade')->nullable();
            $table->text('com_comment')->nullable();

            $table->tinyInteger('fpa_grade')->nullable();
            $table->text('fpa_comment')->nullable();

            $table->tinyInteger('fpm_grade')->nullable();
            $table->text('fpm_comment')->nullable();

            $table->tinyInteger('ltw_grade')->nullable();
            $table->text('ltw_comment')->nullable();

            $table->tinyInteger('psd_grade')->nullable();
            $table->text('psd_comment')->nullable();

            $table->tinyInteger('saw_grade')->nullable();
            $table->text('saw_comment')->nullable();

            $table->tinyInteger('wlm_grade')->nullable();
            $table->text('wlm_comment')->nullable();

            $table->unsignedBigInteger('created_by'); // Added column without foreign key constraint
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deferred_gradings');
    }
};
