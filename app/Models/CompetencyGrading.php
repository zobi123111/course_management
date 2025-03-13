<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyGrading extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'user_id',
        'competency_grade',
        'result',
        'remarks',
        'manager_attention_required',
    ];

    /**
     * Get the lesson associated with the competency grading.
     */
    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }

    /**
     * Get the user associated with the competency grading.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}