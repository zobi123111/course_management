<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseResources extends Model
{
    use HasFactory;
    protected $fillable = [
        'courses_id',
        'resources_id'
    ]; 

    public function course() 
    {
        return $this->belongsTo(Courses::class, 'courses_id');  
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resources_id'); 
    }

 

  

 
}
 