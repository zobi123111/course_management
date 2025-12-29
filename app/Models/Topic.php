<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use HasFactory;

    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'ou_id',
        'course_id',
    ];

    public function questions()
    {
        return $this->hasMany(TopicQuestion::class, 'topic_id');
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
}
