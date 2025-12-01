<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'quiz_questions';
    
    protected $fillable = [
        'quiz_id',
        'topic_id',
        'question_id',
    ];

    // Relationship: A quiz question belongs to a quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function question()
    {
        return $this->belongsTo(TopicQuestion::class, 'question_id');
    }


}
