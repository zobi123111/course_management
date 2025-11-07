<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'option_A',
        'option_B',
        'option_C',
        'option_D',
        'correct_option',
    ];

    // Relationship: A quiz question belongs to a quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // Relationship: A quiz question has many quiz answers
    public function quizAnswers()
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
