<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizTopic extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'quiz_id',
        'topic_id',
        'question_quantity',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function topicQuestions()
    {
        return $this->hasMany(TopicQuestion::class, 'topic_id', 'topic_id');
    }

}
