<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEvents extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'group_id',
        'instructor_id',
        'start_time',
        'end_time'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
