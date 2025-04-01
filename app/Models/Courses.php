<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courses extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['ou_id','course_name','description','duration_type','duration_value','image','status', 'enable_prerequisites'];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function courseLessons()
    {
        return $this->hasMany(CourseLesson::class, 'course_id');
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
}

