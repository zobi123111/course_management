<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'position', 'parent_id'];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
