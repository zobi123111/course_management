<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'assigned_at',
    ];

    // Relationship: A quiz assignment belongs to a quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // Relationship: A quiz assignment belongs to a student
    public function student()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: A quiz assignment has many quiz attempts
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
