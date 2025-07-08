<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CourseDocuments extends Model
{
    use HasFactory;

    protected $fillable = ['course_id','document_name','file_path'];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
}
