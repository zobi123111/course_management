<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachTracksTable extends Migration
{
    public function up()
    {
        Schema::create('teach_tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('user_type', ['instructor', 'examiner', 'both'])->nullable();
            $table->enum('training_type', ['initial', 'recurrent', 'refresher'])->nullable();
            $table->integer('validity')->nullable();
            $table->date('validation_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teach_tracks');
    }
}