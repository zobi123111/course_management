<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEvents extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ou_id',
        'course_id',
        'group_id',
        'instructor_id',
        'start_time',
        'end_time'
    ];

    public function orgUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id'); // Fixed foreign key
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id'); // Fixed foreign key
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id'); // Fixed foreign key
    }
}
