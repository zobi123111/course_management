<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSector extends Model
{
    use HasFactory;

    protected $table = 'lesson_sectors';

    protected $fillable = [
        'lesson_id',
        'lesson_date',
        'departure_airfield',
        'destination_airfield',
        'start_time',
        'takeoff_time',
        'landing_time',
        'end_time',
    ];

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }
}