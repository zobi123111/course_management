<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEvents extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ou_id',
        'course_id',
        'group_id',
        'student_id',
        'instructor_id',
        'resource_id',
        'lesson_ids',
        'event_date',
        'start_time',
        'end_time',
        'departure_airfield',
        'destination_airfield',
        'total_time',
        'std_licence_number',
        'is_locked',
        'student_acknowledged',
        'student_acknowledgement_comments'
    ];

    public function orgUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id'); // Fixed foreign key
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id'); // Fixed foreign key
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id'); // Fixed foreign key
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id', 'id'); // Fixed foreign key
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id'); // Fixed foreign key
    }

        /**
     * Relationship with TaskGrading
     */
    public function taskGradings()
    {
        return $this->hasMany(TaskGrading::class, 'event_id', 'id');
    }

    /**
     * Relationship with CompetencyGrading
     */
    public function competencyGradings()
    {
        return $this->hasMany(CompetencyGrading::class, 'event_id', 'id');
    }

    /**
     * Relationship with OverallAssessment
     */
    public function overallAssessments()
    {   
        return $this->hasMany(OverallAssessment::class, 'event_id', 'id');
    }

    // In App\Models\TrainingEvents
    public function eventLessons()
    {
        return $this->hasMany(TrainingEventLessons::class, 'training_event_id', 'id');
    }

    public function trainingFeedbacks()
    {
        return $this->hasMany(TrainingFeedback::class, 'training_event_id');
    }

    public function firstLesson()
    {
        return $this->hasOne(TrainingEventLessons::class, 'training_event_id')->orderBy('id');
    }

    public function documents()
    {
        return $this->hasMany(TrainingEventDocument::class, 'training_event_id');
    }



}
