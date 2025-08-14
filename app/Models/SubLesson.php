<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SubLesson extends Model
{
    use HasFactory;

    protected $table = 'sub_lessons';

    use SoftDeletes;
    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'grade_type',
        'is_mandatory',
        'status',

    ];


 
    public function lesson()    
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function courseLesson()
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }
}
