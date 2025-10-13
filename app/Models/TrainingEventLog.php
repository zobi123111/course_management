<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingEventLog extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [ 
        'event_id',
        'course_id',
        'lesson_id',
        'user_id',
        'is_locked'
   
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

       public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id', 'id');
    }
}
