<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationUnits extends Model
{
    use HasFactory;
    protected $fillable = ['org_unit_name','description','status'];


    public function users(){
        return $this->hasMany(User::class, 'id', 'ou_id');
    }
    
}
