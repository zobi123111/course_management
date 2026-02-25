<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLicenseValidation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'validation_code_id',
        'country_name',
        'license_number',
        'licence_issued_to',
        'validity_months',
        'issue_date',
        'expiry_date',
        'certificate_file',
        'admin_verification_required',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
