<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'log_type', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}

