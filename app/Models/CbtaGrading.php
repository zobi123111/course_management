<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtaGrading extends Model 
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [ 
        'competency',
        'ou_id',
        'short_name',
        'competency_type' 
    ];

    public function examinerGrading(): HasMany
    {
         return $this->hasMany(ExaminerGrading::class, 'cbta_gradings_id', 'id');
    }

    public function organization_unit()
    {
        return $this->belongsTo(OrganizationUnits::class,'ou_id', 'id');
    }

   
}
