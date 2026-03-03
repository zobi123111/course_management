<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Booking extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'resource', 'start', 'end', 'booking_type', 'resource_type', 'instructor_id', 'status','ou_id','group_id','std_id', 'send_email', 'event_id','course_id','lesson_id','trainingEventLesson_id'
    ]; 


    public function resources()
    {
        return $this->belongsTo(Resource::class,'resource', 'id');  
    }

    public function users()
    {
        return $this->belongsTo(User::class,'std_id', 'id'); 
    }

    public function instructor() {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function organizationUnit() {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function training_event()
    {
        return $this->belongsTo(TrainingEvents::class,'event_id', 'id'); 
    }

    public function Courses()
    {
        return $this->belongsTo(Courses::class,'course_id', 'id'); 
    }

    public function CourseLesson()
    {
        return $this->belongsTo(CourseLesson::class,'lesson_id', 'id'); 
    }

    public function trainingEventLesson()
    {
        return $this->belongsTo(TrainingEventLessons::class,'trainingEventLesson_id', 'id'); 
    }
}
