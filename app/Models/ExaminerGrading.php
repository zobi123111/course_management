<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class ExaminerGrading extends Model 
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'cbta_gradings_id',
        'user_id',
        'competency_type',
        'comment',
        'competency_value',
        'lesson_id'
    ];

    public function cbta(): BelongsTo
    {
        return $this->belongsTo(CbtaGrading::class, 'cbta_gradings_id');
    }

   public function courseLesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id', 'id');
    }
    
}
