<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'user_type',
        'training_type',
        'validity',
        'validation_date',
    ];
}
