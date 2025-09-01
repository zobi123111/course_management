<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeferredGrading extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'deflesson_id',
        'user_id',
        'kno_grade',
        'kno_comment',
        'pro_grade',
        'pro_comment',
        'com_grade',
        'com_comment',
        'fpa_grade',
        'fpa_comment',
        'fpm_grade',
        'fpm_comment',
        'ltw_grade',
        'ltw_comment',
        'psd_grade',
        'psd_comment',
        'saw_grade',
        'saw_comment',
        'wlm_grade',
        'wlm_comment',
        'created_by',
    ];
    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class);
    }

    /**
     * Get the user associated with the competency grading.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(TrainingEvents::class, 'event_id');
    }
}
