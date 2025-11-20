<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizTopic extends Model
{
    use HasFactory;

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

}
