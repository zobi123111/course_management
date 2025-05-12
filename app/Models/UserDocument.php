<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    use HasFactory;

    protected $table = 'user_documents';

    protected $fillable = [
        'user_id',
        'licence',
        'licence_file',
        'licence_admin_verification_required',
        'licence_verified',
        'licence_expiry_date',
        'licence_file_uploaded',
        'licence_non_expiring',
        'licence_invalidate',
        'licence_2',
        'licence_file_2',
        'licence_admin_verification_required_2',
        'licence_verified_2',
        'licence_expiry_date_2',
        'licence_file_uploaded_2',
        'licence_non_expiring_2',
        'licence_2_invalidate',
        'passport',
        'passport_file',
        'passport_admin_verification_required',
        'passport_verified',
        'passport_expiry_date',
        'passport_file_uploaded',
        'passport_invalidate',
        'medical',
        'medical_issuedby',
        'medical_class',
        'medical_issuedate',
        'medical_expirydate',
        'medical_restriction',
        'medical_verified',
        'medical_file',
        'medical_file_uploaded',
        'medical_invalidate',
        'medical_2',
        'medical_issuedby_2',
        'medical_class_2',
        'medical_issuedate_2',
        'medical_expirydate_2',
        'medical_restriction_2',
        'medical_verified_2',
        'medical_file_2',
        'medical_file_uploaded_2',
        'medical_2_invalidate',

    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
}
