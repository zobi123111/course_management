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
        Schema::create('ou_settings', function (Blueprint $table) {
     
        $table->id();
        $table->unsignedBigInteger('organization_id');
        $table->integer('auto_archive')->nullable();
        $table->integer('archive_after_months')->nullable();
        $table->integer('show_dob')->nullable();
        $table->integer('show_phone')->nullable();
        $table->integer('send_email')->nullable();
        $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ou_settings');
    }
};
