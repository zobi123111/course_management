<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPrerequisite extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'prerequisite_detail',
        'prerequisite_type',
    ];

    // Relationship with Course
    public function course()
    {
        return $this->belongsTo(Courses::class);
    }

    // Relationship with CourseLesson
    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }
}
