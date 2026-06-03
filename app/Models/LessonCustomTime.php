<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonCustomTime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lesson_id',
        'course_id',
        'custom_time_id',
        'name',
        'hours',
        'type'
    ];

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }

    public function courseCustomTime()
    {
        return $this->belongsTo(CourseCustomTime::class);
    }

    public function course()
    {
        return $this->belongsTo(Courses::class);
    }

    public function def_lesson()
    {
        return $this->belongsTo(DefLesson::class, 'lesson_id', 'id');
    }

    public function custome_timeCredeted()
    {
        return  $this->hasMany(LessonTimeCredited::class, 'custom_time_id', 'id');
    }


}
