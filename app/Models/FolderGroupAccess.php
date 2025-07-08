<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FolderGroupAccess extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'folder_group_access';

    protected $fillable = [
        'folder_id',
        'group_id',
    ];

    // Optional: Define relationships
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
