<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonBriefingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'file_path',
        'file_name'
    ];

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }
}
