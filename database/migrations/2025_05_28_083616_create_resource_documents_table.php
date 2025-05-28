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
        Schema::create('resource_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->string('name'); // Name given by user
            $table->string('file_path'); // File storage path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_documents');
    }
};
