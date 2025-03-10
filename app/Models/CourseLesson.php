<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseLesson extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['course_id','lesson_title','description','comment','status'];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function sublessons()
    {
        return $this->hasMany(SubLesson::class, 'lesson_id');
    }

}
