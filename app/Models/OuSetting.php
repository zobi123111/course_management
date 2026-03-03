<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class OuSetting extends Model
{
    use HasFactory;
    // use SoftDeletes;
    protected $fillable = [
            'organization_id',
            'auto_archive',
            'archive_after_months',
            'show_dob',
            'show_phone',
            'send_email',
            'timezone',
            'enable_tacho_fields'
    ];
}
