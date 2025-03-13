<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskGrading extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'sub_lesson_id',
        'user_id',
        'task_grade',
    ];

    /**
     * Get the lesson associated with the grading.
     */
    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }

    /**
     * Get the sub-lesson associated with the grading.
     */
    public function subLesson()
    {
        return $this->belongsTo(SubLesson::class);
    }

    /**
     * Get the user associated with the grading.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
