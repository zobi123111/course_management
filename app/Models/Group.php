<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Group extends Model
{
    use HasFactory;

    protected $fillable = ['ou_id', 'name', 'user_ids', 'status'];

    protected $casts = [
        'user_ids' => 'array', // Automatically converts JSON to array
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'ou_id', 'ou_id');
    }
}