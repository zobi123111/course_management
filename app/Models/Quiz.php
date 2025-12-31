<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory;
    
    use SoftDeletes;

    protected $table = 'quizs';

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'duration',
        'passing_score',
        'quiz_type',
        'status',
        'show_result',
        'created_by',
        'ou_id',
    ];

    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    public function quizAssignments()
    {
        return $this->hasMany(QuizAssignment::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // public function quizAttempts()
    // {
    //     return $this->belongsTo(QuizAttempt::class);
    // }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class);
    }

    public function quizOu()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }

    public function topics()
    {
        return $this->hasMany(QuizTopic::class, 'quiz_id');
    }

    public function topicQuestions()
    {
        return $this->hasManyThrough(TopicQuestion::class, QuizQuestion::class, 'quiz_id', 'id', 'id', 'question_id');
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function trainingQuizzes()
    {
        return $this->hasMany(TrainingQuiz::class, 'quiz_id');
    }
}
