<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'training_event_id',
        'user_id',
        'question_id',
        'answer',
    ];

    /**
     * Get the training event related to this feedback.
     */
    public function event()
    {
        return $this->belongsTo(TrainingEvent::class, 'training_event_id');
    }

    /**
     * Get the user (student) who gave this feedback.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the feedback question.
     */
    public function question()
    {
        return $this->belongsTo(TrainingFeedbackQuestion::class, 'question_id');
    }

}
