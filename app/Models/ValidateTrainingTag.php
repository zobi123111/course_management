<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidateTrainingTag extends Model
{
    use HasFactory;
    protected $fillable = ['event_id','course_id','tag_id', 'validate_status'];
}
