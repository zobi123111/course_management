<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['ou_id', 'parent_id', 'folder_name', 'description', 'status','is_published'];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->hasMany(Folder::class, 'parent_id')->with('childrenRecursive');
    }

    // In Folder.php
    public function groups() {  
        return $this->belongsToMany(Group::class, 'folder_group_access', 'folder_id', 'group_id');
    }
}
