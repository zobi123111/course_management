<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserQuiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_quizzes';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'quiz_details',
    ];

    protected $casts = [
        'quiz_details' => 'array',
    ];
}

