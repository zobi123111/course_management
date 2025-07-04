<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use HasFactory;
    use SoftDeletes;
    
 protected $fillable = [
        'name',
        'status',
        'kind_of_rating',
        'is_fixed_wing',
        'is_rotary',
        'is_instructor',
        'is_examiner',
        'ou_id',
    ];

  public function parents()
{
    return $this->belongsToMany(Rating::class, 'parent_rating', 'rating_id', 'parent_id');
}

public function children()
{
    return $this->belongsToMany(Rating::class, 'parent_rating', 'parent_id', 'rating_id');
}





}
