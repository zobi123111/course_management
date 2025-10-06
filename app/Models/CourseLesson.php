<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseLesson extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['course_id','lesson_title','description','grade_type', 'lesson_type', 'custom_time_id', 'enable_cbta', 'comment','status', 'enable_prerequisites','instructor_cbta', 'examiner_cbta'];

    public function course()
    { 
        return $this->belongsTo(Courses::class, 'course_id');
    } 

    public function sublessons()
    {
        return $this->hasMany(SubLesson::class, 'lesson_id')->orderBy('position'); 
    }

    public function prerequisites()
    {
        return $this->hasMany(LessonPrerequisite::class, 'lesson_id');
    }

    public function prerequisiteDetails()
    {
        return $this->hasMany(LessonPrerequisiteDetail::class, 'lesson_id')
        ->where('created_by', auth()->id());
    }

    public function customTime()
    {
        return $this->belongsTo(CourseCustomTime::class, 'custom_time_id');
    }

    public function courseLesson()
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function examinerGradings(): HasMany
    {
        return $this->hasMany(ExaminerGrading::class, 'lesson_id', 'id');
    }

}
