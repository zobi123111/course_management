<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCustomTime extends Model
{
    use HasFactory;
    protected $fillable = ['course_id','name','hours'];

    public function course()
    {
        return $this->belongsTo(Courses::class);
    }
}
