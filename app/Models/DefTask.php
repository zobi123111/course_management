<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefTask extends Model
{
    use HasFactory;

     use HasFactory;

    protected $table = 'def_tasks';

    protected $fillable = [
        'event_id',
        'user_id',       // typically the student_id
        'task_id',       // typically sub_lesson_id or task_grading_id
        'created_by',
    ];

    /**
     * Relationships
     */

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

    public function grading()
    {
        return $this->hasOne(TaskGrading::class, 'sub_lesson_id', 'task_id');
    }
}
