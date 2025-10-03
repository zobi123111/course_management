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
        'course_end_date',
        'recommended_by_instructor_id',
        'start_time',
        'end_time',
        'departure_airfield',
        'destination_airfield',
        'total_time',
        'simulator_time',   
        'std_license_number',
        'is_locked',
        'student_acknowledged',
        'student_acknowledgement_comments',
        'entry_source'
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

    public function studentDocument()
    {
        return $this->hasOne(UserDocument::class, 'user_id', 'student_id');
    }

    public function recommendedInstructor()
    {
        return $this->belongsTo(User::class, 'recommended_by_instructor_id');
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
            return $this->hasMany(TrainingEventLessons::class, 'training_event_id', 'id')
                ->join('course_lessons', 'training_event_lessons.lesson_id', '=', 'course_lessons.id') 
                ->join('resources', 'training_event_lessons.resource_id', '=', 'resources.id')
                ->orderBy('course_lessons.position')
                ->select(
                    'training_event_lessons.*',
                    'resources.name as resource_name', 
                    'course_lessons.lesson_type'
                )
                ->with('instructorDocuments'); 
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


    public function getIsGradedAttribute() 
    {
        // Check if any lesson in this event has CBTA enabled
        $cbtaEnabled = $this->eventLessons->contains(function ($eventLesson) {
            return $eventLesson->lesson?->enable_cbta;
        });

        $hasTaskGrading = $this->task_gradings_count > 0;
        $hasCompetencyGrading = $this->competency_gradings_count > 0;

        if ($cbtaEnabled) {
            return $hasTaskGrading && $hasCompetencyGrading;
        }

        return $hasTaskGrading;
    }

    public function getCanEndCourseAttribute()
    {
        $studentId = $this->student_id;
        $allTasksGraded = $this->eventLessons->every(function ($eventLesson) use ($studentId) {
                        // If lesson is null, consider tasks not graded
                        if (!$eventLesson->lesson) {
                            return false;
                        }

                        return $eventLesson->lesson->subLessons->every(function ($subLesson) use ($eventLesson, $studentId) {
                            return \App\Models\TaskGrading::where([
                                'event_id'      => $eventLesson->training_event_id,
                                'lesson_id'     => $eventLesson->lesson_id,
                                'sub_lesson_id' => $subLesson->id,
                                'user_id'       => $studentId,
                            ])->exists();
                        });
                    });
      


        // B. Check competency grading if enabled for any lesson
        $cbtaEnabled = $this->eventLessons->contains(function ($eventLesson) {
            return $eventLesson->lesson?->enable_cbta;
        });

        $competencyOk = true;
 
        if ($cbtaEnabled) {
            $grading = \App\Models\CompetencyGrading::where([
                'event_id' => $this->id,
                'user_id'  => $studentId,
            ])->first();

            $competencyOk = $grading &&
                $grading->kno_grade !== null &&
                $grading->pro_grade !== null &&
                $grading->com_grade !== null &&
                $grading->fpa_grade !== null &&
                $grading->fpm_grade !== null &&
                $grading->ltw_grade !== null &&
                $grading->psd_grade !== null &&
                $grading->saw_grade !== null &&
                $grading->wlm_grade !== null;
        }

        // C. Check overall assessment for one_event courses
        $assessmentOk = true;
      
        if ($this->course?->course_type === 'one_event') { 
            $assessmentOk = $this->overallAssessments()
                ->where('user_id', $studentId)
                ->exists();
        }
      
       // return $allTasksGraded && $competencyOk && $assessmentOk && !$this->is_locked;
           return $allTasksGraded && $assessmentOk && !$this->is_locked;
    }


        public function defLessonTasks()
        {
            return $this->hasMany(DefLessonTask::class, 'event_id', 'id');
        }

        // public function getAllTaskGradingsAttribute()
        // {
        //     return $this->taskGradings->merge($this->defLessonTasks);
        // }

         public function defLessons()
        {
            return $this->hasMany(DefLesson::class, 'event_id', 'id');
        }

        public function  def_lesson_tasks()
        {
            return $this->hasMany(DefLessonTask::class, 'task_id', 'id');
        }


        public function deferredGradings()
        {
            return $this->hasMany(DeferredGrading::class, 'event_id', 'id');
        }

}
