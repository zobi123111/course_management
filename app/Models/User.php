<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'passport_required',
        'passport',
        'passport_file',
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

    public function organization(){
        return $this->belongsTo(OrganizationUnits::class, 'ou_id', 'id');
    }


    public function roles(){
        return $this->belongsTo(Role::class, 'role', 'id');
    }
    
}
