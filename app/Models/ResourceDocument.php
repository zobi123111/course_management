<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'name',
        'file_path',
    ];

    // Define the relationship to Resource (assuming you have a Resource model)
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
