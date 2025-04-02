<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseTemplateField extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['template_id', 'field_name', 'grading_type'];

    public function template()
    {
        return $this->belongsTo(CourseTemplate::class, 'template_id');
    }



}
