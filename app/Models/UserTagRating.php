<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTagRating extends Model
{
     use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'event_id',
        'course_id',
        'tag_id',
        'tag_validity',
        'tag_type',
        'tag_expiry_date'
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function rhsTag()
    {
        return $this->belongsTo(RhsTag::class, 'tag_id');
    }

}
