<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOpcRating extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_opc_ratings';

    protected $fillable = [
        'user_id',
        'event_id',
        'course_id',
        'aircraft_type',
        'opc_expiry_date',
    ];

    protected $casts = [
        'opc_expiry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
