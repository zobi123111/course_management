<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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


    public function getExpiryStatus($date, $nonExpiring = false)
    {
        if ($nonExpiring) {
            return 'Green'; // Non-expiring is always valid
        }

        if (!$date) return 'N/A';

        $expiryDate = Carbon::parse($date);
        $now = now();

        if ($expiryDate->lt($now)) {
            return 'Red';
        }

        if ($expiryDate->diffInDays($now) < 90) {
            return 'Yellow';
        }

        return 'Green';
    }

    /**
     * Accessors for Expiry Status
    */
    
    public function getLicenceStatusAttribute()
    {
        return $this->getExpiryStatus($this->licence_expiry_date, $this->licence_non_expiring);
    }

    public function getLicence2StatusAttribute()
    {
        return $this->getExpiryStatus($this->licence_expiry_date_2, $this->licence_non_expiring_2);
    }

    public function getPassportStatusAttribute()
    {
        return $this->getExpiryStatus($this->passport_expiry_date);
    }

    public function getMedicalStatusAttribute()
    {
        return $this->getExpiryStatus($this->medical_expirydate);
    }

    public function getMedical2StatusAttribute()
    {
        return $this->getExpiryStatus($this->medical_expirydate_2);
    }

    /**
     * Check if Documents Are Expiring
    */

    public function isLicenceExpiring()
    {
        return !$this->licence_non_expiring && in_array($this->licence_status, ['Red', 'Yellow']);
    }

    public function isLicence2Expiring()
    {
        return !$this->licence_non_expiring_2 && in_array($this->licence_2_status, ['Red', 'Yellow']);
    }
    
    public function isMedicalExpiring()
    {
        return in_array($this->medical_status, ['Red', 'Yellow']);
    }

    public function isMedical2Expiring()
    {
        return in_array($this->medical_2_status, ['Red', 'Yellow']);
    }
    
    public function isPassportExpiring()
    {
        return in_array($this->passport_status, ['Red', 'Yellow']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    
}
