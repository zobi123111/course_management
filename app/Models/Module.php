<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'route_name', 'page_id'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}
