<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CbtaGrading extends Model 
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'competency',
        'short_name',
        'competency_type'
    ];
}
