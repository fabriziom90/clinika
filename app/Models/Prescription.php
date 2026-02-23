<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_entry_id',
        'drug_name',
        'dosage',
        'frequency',
        'duration',
        'notes',
    ];

    public function medicalEntry()
    {
        return $this->belongsTo(MedicalEntry::class);
    }
}
