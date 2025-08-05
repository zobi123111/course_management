<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserRating extends Model
{
    use HasFactory;
 
    protected $table = 'user_ratings';

    protected $fillable = [
        'user_id',
        'rating_id',
        'parent_id',
        'issue_date',
        'expiry_date', 
        'file_path',
        'admin_verified',
        'linked_to',
        'verify_rating'
    ];

    public function parentRating()
    {
        return $this->belongsTo(Rating::class, 'parent_id');
    }


    public function getExpiryStatusAttribute() 
    {
        if (!$this->expiry_date) return 'N/A';

        $expiryDate = Carbon::parse($this->expiry_date);
        $now = now();

        if ($expiryDate->lt($now)) {
            return 'Red'; 
        }

        if ($expiryDate->diffInDays($now) <= 30) { 
            return 'Orange';
        }

        if ($expiryDate->diffInDays($now) <= 90) {
            return 'Amber';
        }

        return 'Blue';  // Valid
    }

    /**
     * Get the user that owns the rating.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the rating details.
     */
      public function rating() 
    {
        return $this->belongsTo(Rating::class, 'rating_id');
    }

    //  public function parentrating() 
    // {
    //     return $this->belongsTo(Rating::class, 'parent_id');
    // }

      public function parent()
    {
        return $this->belongsTo(Rating::class, 'parent_id'); 
    }

}