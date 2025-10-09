<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEventLog extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'event_id',
        'course_id',
        'lesson_id',
        'user_id',
        'is_locked'
   
    ];
}
