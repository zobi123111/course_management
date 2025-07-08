<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingEventDocument extends Model
{
    use HasFactory;    
    protected $fillable = [
        'training_event_id',
        'course_document_id',
        'file_path',
    ];

    public function trainingEvent()
    {
        return $this->belongsTo(TrainingEvent::class);
    }

    public function courseDocument()
    {
        return $this->belongsTo(CourseDocuments::class);
    }
}
