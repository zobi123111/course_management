<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationUnits extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['org_unit_name','description','status', 'org_logo'];


    public function users(){
        return $this->hasMany(User::class, 'ou_id', 'id');
    }

    public function roleOneUsers()
    {
        return $this->hasOne(User::class, 'ou_id', 'id')->where('role', 1);
    }    

    public function admin()
    {
        return $this->hasOne(User::class, 'ou_id', 'id')->where('is_admin', 1);
    }

}
