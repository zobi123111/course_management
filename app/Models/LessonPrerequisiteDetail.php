<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPrerequisiteDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'prereq_id',
        'course_id',
        'lesson_id',
        'prerequisite_type',
        'prerequisite_detail',
        'file_path',
        'created_by',
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

    // Relationship with User (Creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
