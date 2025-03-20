<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['ou_id', 'folder_id', 'group_id', 'doc_title', 'version_no', 'issue_date', 'expiry_date', 'document_file', 'original_filename', 'acknowledged', 'status'];
}
