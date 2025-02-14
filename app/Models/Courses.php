<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courses extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['ou_id','course_name','description','image','status'];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function courseLessons()
    {
        return $this->hasMany(CourseLesson::class, 'course_id');
    }
}
