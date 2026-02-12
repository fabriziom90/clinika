<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VitalParameter extends Model
{
    protected $fillable = [
        'medical_entry_id', 'pressure', 'heart_rate',
        'temperature', 'weight', 'height',
    ];

    public function medicalEntry()
    {
        return $this->belongsTo(MedicalEntry::class);
    }
}
