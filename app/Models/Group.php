<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['ou_id', 'name', 'user_ids', 'status'];

    protected $casts = [
        'user_ids' => 'array', // Automatically converts JSON to array
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'ou_id', 'ou_id');
    }

    public function courses() 
    {
        return $this->belongsToMany(Courses::class, 'courses_group')->withTimestamps();
    }

    public function ounit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id', 'id');
    }

    public function folders()
    {
        return $this->belongsToMany(Folder::class, 'folder_group_access', 'group_id', 'folder_id');
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'document_group');
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_resource', 'resource_id', 'group_id');
    }
}