<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyGrading extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'lesson_id',
        'user_id',
        'competency_grade',
        'created_by',
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