<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;
    protected $fillable = ['name','registration', 'type', 'classroom', 'class', 'other', 'note', 'hours_from_rts', 'date_from_rts', 'date_for_maintenance', 'hours_remaining','resource_logo', 'enable_doc_upload', 'ou_id']; 

    public function courseResources()
    {
        return $this->hasMany(CourseResources::class, 'resources_id');
    }

    public function BookedResources()
    {
        return $this->hasMany(BookedResource::class, 'resources_id');
    }

    public function orgUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function documents()
    {
        return $this->hasMany(ResourceDocument::class);
    }
}



