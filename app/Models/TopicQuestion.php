<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopicQuestion extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'topic_id',
        'question_text',
        'question_image',
        'question_type',
        'option_type',
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
