<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OuRating extends Model
{
    use HasFactory; 
        protected $fillable = [ 
        'ou_id',
        'rating_id'
    ];


    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class);
    }

    public function organization_unit(): BelongsTo 
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id', 'id');
    }
}
