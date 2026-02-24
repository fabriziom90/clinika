<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VitalParameter extends Model
{
    protected $fillable = [
        'medical_entry_version_id', 'pressure', 'heart_rate',
        'temperature', 'weight', 'height',
    ];

    public function version()
    {
        return $this->belongsTo(MedicalEntryVersion::class, 'medical_entry_version_id');
    }
}
