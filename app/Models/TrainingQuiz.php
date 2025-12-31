<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingQuiz extends Model
{
    use HasFactory;

    protected $table = 'training_quizzes';

    protected $fillable = [
        'trainingevent_id',
        'student_id',
        'quiz_id',
        'is_active',
    ];

    public function trainingEvent()
    {
        return $this->belongsTo(TrainingEvents::class, 'trainingevent_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

}
