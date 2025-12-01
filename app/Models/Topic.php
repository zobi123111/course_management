<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'ou_id',
    ];

    public function questions()
    {
        return $this->hasMany(TopicQuestion::class, 'topic_id');
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

}
