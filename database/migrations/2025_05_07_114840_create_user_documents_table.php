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
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); 
        
            // Licence fields
            $table->string('licence')->nullable();
            $table->string('licence_file')->nullable();
            $table->boolean('licence_admin_verification_required')->default(0);
            $table->boolean('licence_verified')->default(0);
            $table->date('licence_expiry_date')->nullable();
            $table->boolean('licence_file_uploaded')->default(0);
            $table->boolean('licence_non_expiring')->default(0);
            $table->boolean('licence_invalidate')->default(0);
            
            // Second Licence fields
            $table->string('licence_2')->nullable();
            $table->string('licence_file_2')->nullable();
            $table->boolean('licence_admin_verification_required_2')->default(0);
            $table->boolean('licence_verified_2')->default(0);
            $table->date('licence_expiry_date_2')->nullable();
            $table->boolean('licence_file_uploaded_2')->default(0);
            $table->boolean('licence_non_expiring_2')->default(0);
            $table->boolean('licence_2_invalidate')->default(0);
                    
            // Passport fields
            $table->string('passport')->nullable();
            $table->string('passport_file')->nullable();
            $table->boolean('passport_admin_verification_required')->default(0);
            $table->boolean('passport_verified')->default(0);
            $table->date('passport_expiry_date')->nullable();
            $table->boolean('passport_file_uploaded')->default(0);
            $table->boolean('passport_invalidate')->default(0);
                   
            // Medical 1
            $table->string('medical')->nullable();
            $table->string('medical_issuedby')->nullable();
            $table->string('medical_class')->nullable();
            $table->date('medical_issuedate')->nullable();
            $table->date('medical_expirydate')->nullable();
            $table->string('medical_restriction')->nullable();
            $table->boolean('medical_verified')->default(0);
            $table->string('medical_file')->nullable();
            $table->boolean('medical_file_uploaded')->default(0);
            $table->boolean('medical_invalidate')->default(0);
        
            // Medical 2
            $table->string('medical_2')->nullable();
            $table->string('medical_issuedby_2')->nullable();
            $table->string('medical_class_2')->nullable();
            $table->date('medical_issuedate_2')->nullable();
            $table->date('medical_expirydate_2')->nullable();
            $table->string('medical_restriction_2')->nullable();
            $table->boolean('medical_verified_2')->default(0);
            $table->string('medical_file_2')->nullable();
            $table->boolean('medical_file_uploaded_2')->default(0);
            $table->boolean('medical_2_invalidate')->default(0);
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};
