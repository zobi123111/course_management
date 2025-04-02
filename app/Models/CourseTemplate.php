<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ou_id',
        'name',
        'description',
        'enable_cbta',
        'enable_manual_time_entry'
    ];
}
