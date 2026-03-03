<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licence_validation_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ou_id')->nullable();
            $table->string('code');     
            $table->string('country_name');
            $table->string('aircraft_prefix');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licence_validation_types');
    }
};
