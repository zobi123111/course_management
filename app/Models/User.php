<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use SoftDeletes;
    protected $fillable = [
        'fname',
        'lname',
        'name',
        'role',
        'email',
        'image',
        'licence_required',
        'licence', 
        'licence_file', 
        'licence_admin_verification_required',
        'licence_verified',
        'licence_expiry_date',
        'licence_non_expiring',
        'passport_required',
        'passport',
        'passport_file',
        'passport_admin_verification_required',
        'passport_verified',
        'passport_expiry_date',
        'rating_required',
        'rating',
        'currency_required',
        'currency',
        'custom_field_name',
        'custom_field_value',
        'status',
        'password',
        'password_flag',
        'ou_id',
        'extra_roles',
        'custom_field_required',
        'custom_field_date',
        'custom_field_text',
        'custom_field_admin_verification_required',
        'medical',
        'medical_adminRequired',
        'medical_issuedby',
        'medical_class',
        'medical_issuedate',
        'medical_expirydate',
        'medical_restriction',
        'medical_verified',
        'licence_file_uploaded',
        'passport_file_uploaded',
        'medical_file',
        'medical_file_uploaded',
        'is_admin'  
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // public function getExpiryStatus($date)
    // {
    //   //  dd($date);
    //     if (!$date) return 'N/A';

    //     $expiryDate = Carbon::parse($date);
    //     $now = now();

    //     if ($expiryDate->lt($now)) {
    //         return '<span style="color: red;"><i class="bi bi-check-circle-fill"></i> Expired</span>'; 
    //     }

    //     if ($expiryDate->diffInDays($now) <= 30) {
    //         return '<span style="color: red;"><i class="bi bi-check-circle-fill"></i> Expiring Soon!</span>';
    //     }

    //     if ($expiryDate->diffInDays($now) <= 90) {
    //         return '<span style="color: orange;"><i class="bi bi-check-circle-fill"></i> Expiring in 3 Months</span>';
    //     }

    //     return '<span style="color: green;"><i class="bi bi-check-circle-fill"></i> Valid</span>';
    // }


    // // Expiry Date Fucntion Start //

    // public function getLicenceStatusAttribute()
    // {
    //     return $this->getExpiryStatus($this->licence_expiry_date);
    // }


    // public function getPassportStatusAttribute()
    // {
    //     return $this->getExpiryStatus($this->passport_expiry_date);
    // }

    // public function getMedicalStatusAttribute()
    // {
    //     return $this->getExpiryStatus($this->medical_expirydate);
    // }

    public function getExpiryStatus($date)
    {
        if (!$date) return 'N/A';

        $expiryDate = Carbon::parse($date);
        $now = now();

        if ($expiryDate->lt($now)) {
            return 'Red'; 
        }

        if ($expiryDate->diffInDays($now) <= 30) {
            return 'Orange';
        }

        if ($expiryDate->diffInDays($now) <= 90) {
            return 'Amber';  // Expiring in 3 Months
        }

        return 'Blue';  // Valid
    }

    /**
     * Accessors for Expiry Status
     */
    public function getLicenceStatusAttribute()
    {
        return $this->getExpiryStatus($this->licence_expiry_date);
    }

    public function getPassportStatusAttribute()
    {
        return $this->getExpiryStatus($this->passport_expiry_date);
    }

    public function getMedicalStatusAttribute()
    {
        return $this->getExpiryStatus($this->medical_expirydate);
    }

    /**
     * Check if Documents Are Expiring
     */
    public function isLicenceExpiring()
    {
        return in_array($this->licence_status, ['Red', 'Orange', 'Amber']);
    }

    public function isMedicalExpiring()
    {
        return in_array($this->medical_status, ['Red', 'Orange', 'Amber']);
    }

    public function isPassportExpiring()
    {
        return in_array($this->passport_status, ['Red', 'Orange', 'Amber']);
    }

    // Expiry Date Fucntion End //


    public function organization(){
        return $this->belongsTo(OrganizationUnits::class, 'ou_id', 'id');
    }


    public function roles(){
        return $this->belongsTo(Role::class, 'role', 'id');
    }

    public function taskGrades()
    {
        return $this->hasMany(TaskGrading::class, 'user_id');
    }

    public function competencyGrades()
    {
        return $this->hasMany(CompetencyGrading::class, 'user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class, 'user_id');
    }

    public function usrRatings()
    {
        return $this->hasMany(UserRating::class);
    }

    public function documents()
    {
        return $this->hasMany(UserDocument::class, 'user_id');
    }
    
}
