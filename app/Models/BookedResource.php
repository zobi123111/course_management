<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookedResource extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'ou_id',
        'course_id',
        'resource_id',
        'start_date',
        'end_date',
        'status' 
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    

}
