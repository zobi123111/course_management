<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEventReview extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [ 
        'event_id',
        'user_id',
        'review' 
    ];


    public function users(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
