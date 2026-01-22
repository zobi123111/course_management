<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhsTag extends Model 
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'rhstag'
    ];

    public function userTagRatings()
    {
        return $this->hasMany(UserTagRating::class, 'tag_id');
    }
}
