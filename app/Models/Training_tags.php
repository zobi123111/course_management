<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training_tags extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'event_id',
        'course_id',
        'tag_id',
        'aircraft_type',
        'tag_expiry_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rhsTag()
    {
        return $this->belongsTo(RhsTag::class, 'tag_id', 'id');
    }
}
