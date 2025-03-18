<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverallAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'result',
        'remarks',
    ];

    // Define relationships
    public function event()
    {
        return $this->belongsTo(TrainingEvent::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
