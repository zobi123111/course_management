<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskGrading extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'lesson_id',
        'sub_lesson_id',
        'user_id',
        'task_grade',
        'is_def_task',
        'task_comment',
        'created_by',
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

    /**
     * Get the training-event associated with the grading.
     */
    public function event()
    {
        return $this->belongsTo(TrainingEvents::class, 'event_id');
    }
}
