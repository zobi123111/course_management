<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenceValidationType extends Model
{
    protected $table = 'licence_validation_types';

    protected $fillable = [
        'ou_id',
        'code',
        'country_name',
        'aircraft_prefix',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function OrganizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'ou_id');
    }

}
