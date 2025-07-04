<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class ParentRating extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parent_rating';

    protected $fillable = [
        'rating_id',
        'parent_id',
    ];

    // Rating this parent link belongs to
    public function rating()
    {
        return $this->belongsTo(Rating::class, 'rating_id');
    }

    // Parent rating (the actual parent)
    public function parent()
    {
        return $this->belongsTo(Rating::class, 'parent_id');
    }
    public function child()
{
    return $this->belongsTo(Rating::class, 'rating_id');
}

}

