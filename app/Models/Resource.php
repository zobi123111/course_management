<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;
    protected $fillable = ['name','registration', 'type', 'class', 'other', 'note', 'hours_from_rts', 'date_from_rts', 'date_for_maintenance',   'hours_remaining','resource_logo','ou_id']; 

  
    public function courseResources()
    {
        return $this->hasMany(CourseResources::class, 'resources_id');
    }

    public function BookedResources()
    {
        return $this->hasMany(BookedResource::class, 'resources_id');
    }
}



