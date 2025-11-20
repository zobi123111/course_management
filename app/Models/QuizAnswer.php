<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'question_id',
        'selected_option',
        'is_correct',
    ];

    // Relationship: A quiz answer belongs to a quiz attempt
    public function quizAttempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    // Relationship: A quiz answer belongs to a quiz question
    public function quizQuestion()
    {
        return $this->belongsTo(QuizQuestion::class);
    }
}
