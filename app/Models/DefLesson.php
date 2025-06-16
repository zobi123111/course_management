<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefLesson extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'def_lessons';

    protected $fillable = [
        'event_id',
        'user_id',         // student ID
        'task_ids',         // sub_lesson_id or task reference
        'instructor_id',
        'resource_id',
        'lesson_title',
        'lesson_date',
        'start_time',
        'end_time',
        'created_by',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'task_ids' => 'array',
    ];

    /**
     * Relationships
     */
    public function event()
    {
        return $this->belongsTo(TrainingEvents::class, 'event_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }



    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTasksAttribute()
    {
        if (!$this->task_id || !is_array($this->task_id)) {
            return collect();
        }

        return SubLesson::whereIn('id', $this->task_id)->get();
    }


}
