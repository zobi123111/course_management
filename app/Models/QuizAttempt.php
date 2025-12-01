<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizAttempt extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'started_at',
        'submitted_at',
        'score',
        'status',
        'result',
    ];

    // Relationship: A quiz attempt belongs to a quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // Relationship: A quiz attempt belongs to a student
    public function student()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: A quiz attempt has many quiz answers
    public function quizAnswers()
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
