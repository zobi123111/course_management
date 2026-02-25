<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEventLessons extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'training_event_id',
        'lesson_id',
        'instructor_id',
        'resource_id',
        'lesson_date',
        'start_time',
        'end_time',
        'hours_credited',
        'custom_hours_credited',
        'departure_airfield',
        'destination_airfield',
        'instructor_license_number',
        'is_locked',
        'operation1',
        'role1',
        'operation2',
        'role2',
        'overall_result',
        'overall_remark',
        'takeoff_time',
        'landing_time',
        'tacho_start_time',
        'tacho_stop_time'
    ];

    // protected $dates = ['lesson_date', 'start_time', 'end_time', 'total_time', 'deleted_at'];

    // Relationships

    public function trainingEvent()
    {
        return $this->belongsTo(TrainingEvents::class);
    }

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }

    public function instructorDocuments()
    {
        return $this->hasMany(UserDocument::class, 'user_id', 'instructor_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'lesson_id', 'lesson_id');
    }

}
