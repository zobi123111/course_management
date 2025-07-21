<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    return $this->hasMany(\App\Models\ParentRating::class, 'parent_id');
}

public function associatedChildren()
{
    return $this->belongsToMany(
        Rating::class,
        'user_ratings',
        'parent_id',  
        'rating_id'   
    );
}



// ✅ From child to parent (this model is the child)
public function associatedParents()
{
    return $this->belongsToMany(
        Rating::class,
        'parent_rating',
        'rating_id',   
        'parent_id'  
    );
}
 
public function ou_ratings(): HasMany
{
     return $this->hasMany(OuRating::class);
}

public function childRatings() 
{
    return $this->hasManyThrough(
        Rating::class,
        ParentRating::class,
        'parent_id',    // Foreign key on parent_rating table
        'id',           // Foreign key on ratings table
        'id',           // Local key on ratings table
        'rating_id'     // Local key on parent_rating table
    );
}
public function parentLinks()
{
    return $this->hasMany(ParentRating::class, 'parent_id');
}

public function childLinks()
{
    return $this->hasMany(ParentRating::class, 'rating_id');
}


}
