<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;


class CourseGroup extends Pivot
{
    use HasFactory, SoftDeletes;

    protected $table = 'courses_group';

    protected $fillable = [
        'course_id',
        'group_id',
    ];
}
