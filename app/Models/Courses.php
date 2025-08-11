<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courses extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['ou_id','course_type','course_name','description','duration_type','duration_value','enable_groundschool_time', 'groundschool_hours', 'enable_simulator_time', 'simulator_hours', 'enable_custom_time_tracking', 'image','enable_feedback','enable_instructor_upload', 'status', 'enable_prerequisites', 'position', 'ato_num'];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function courseLessons()
    {
        return $this->hasMany(CourseLesson::class, 'course_id')->orderBy('position');
    }

    public function groups() 
    {
        return $this->belongsToMany(Group::class, 'courses_group')->withTimestamps();  
    }


    public function resources()  
    {
        return $this->belongsToMany(Resource::class, 'course_resources', 'courses_id', 'resources_id')->withTimestamps();  
    } 
    
    
    public function courseGroups()
    {
        return $this->hasMany(CourseGroup::class, 'courses_id');
    }
    // public function groups()
    // {
    //     return $this->belongsToMany(Group::class);

    // }

    public function coursesResources() 
    {
        return $this->belongsToMany(CourseResources::class, 'course_resources')->withTimestamps();  
    }

    public function prerequisites() 
    {
        return $this->hasMany(CoursePrerequisite::class, 'course_id'); 
    }
    
    public function prerequisiteDetails()
    {
        return $this->hasMany(CoursePrerequisiteDetail::class, 'course_id')
        ->where('created_by', auth()->id());
    }

    public function training_feedback_questions()
    {
        return $this->hasMany(TrainingFeedbackQuestion::class, 'course_id');
    }

    public function documents()
    {
        return $this->hasMany(CourseDocuments::class, 'course_id');
    }

    public function getCourseStudents($ouId = null)
    {
        $groupIds = $this->groups->pluck('id')->toArray();

        $userIds = Group::whereIn('id', $groupIds)
            ->where('ou_id', $ouId ?? $this->ou_id) // use parameter or fallback to course's ou_id
            ->pluck('user_ids')
            ->flatten()
            ->unique()
            ->toArray();

        $users = User::whereIn('id', $userIds)->get();

        return $users->filter(function ($user) {
            return get_user_role($user->role_id) === 'student';
        })->values();
    }

    public function trainingEvents()
    {
        return $this->hasMany(TrainingEvents::class, 'course_id');
    }

    public function customTimes()
    {
        return $this->hasMany(CourseCustomTime::class, 'course_id');
    }

}

