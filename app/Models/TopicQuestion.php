<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
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

    public function quizAnswers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

}
