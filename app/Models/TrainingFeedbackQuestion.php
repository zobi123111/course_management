<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TrainingFeedbackQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'question',
        'answer_type',
    ];

    /**
     * Get the course that owns the feedback question.
     */
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
}
