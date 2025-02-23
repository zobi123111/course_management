<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // protected $fillable = ['role_name', 'status'];
    protected $fillable = ['role_name','status'];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    public function users(){
        return $this->hasMany(User::class, 'role', 'id');
    }
}
