<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePrerequisite extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'prerequisite_detail', 'prerequisite_type'];

    public function course()
    {
        return $this->belongsTo(Courses::class);
    }
}
