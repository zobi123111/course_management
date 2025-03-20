<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePrerequisiteDetail extends Model
{
    use HasFactory;

    protected $table = 'course_prerequisite_details';

    protected $fillable = [
        'course_id',
        'prerequisite_type',
        'prerequisite_detail',
        'file_path',
        'created_by',
    ];

    /**
     * Get the course associated with this prerequisite detail.
     */
    public function course()
    {
        return $this->belongsTo(Courses::class);
    }

    /**
     * Get the user who created the prerequisite detail.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
