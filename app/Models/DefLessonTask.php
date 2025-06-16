<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefLessonTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'def_lesson_tasks';

    protected $fillable = [
        'def_lesson_id',
        'event_id',
        'user_id',         // student_id
        'task_id',         // refers to task_grading or sub_lesson_id
        'task_grade',
        'task_comment',
        'created_by',
    ];

    /**
     * Relationships
     */

    public function defLesson()
    {
        return $this->belongsTo(DefLesson::class, 'def_lesson_id');
    }

    public function event()
    {
        return $this->belongsTo(TrainingEvents::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(SubLesson::class, 'task_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }



}
