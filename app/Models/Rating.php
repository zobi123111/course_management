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
    return $this->belongsToMany(Rating::class, 'parent_rating', 'parent_id', 'rating_id');
}
// app/Models/Rating.php

public function associatedChildren()
{
    return $this->belongsToMany(
        Rating::class,          // Related model (self-reference)
        'parent_rating',        // Pivot table name
        'parent_id',            // Foreign key on the pivot table for THIS model
        'rating_id'             // Foreign key for the associated (child) ratings
    );
}

public function associatedParents()
{
    return $this->belongsToMany(
        Rating::class,
        'parent_rating',
        'rating_id',            // Foreign key on the pivot for THIS model
        'parent_id'             // Foreign key for the parent
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









}
