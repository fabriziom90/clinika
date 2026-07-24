<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientConsent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'consent_type_id', 'consent_version_id', 'status', 'acquisition_method', 'recorded_by', 'pdf_path'
    ];

    protected $casts = [
        'deleted_at' => 'datetime'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consentType()
    {
        return $this->belongsTo(ConsentType::class);
    }

    public function consentVersion(){
        return $this->belongsTo(ConsentVersion::class);
    }

    public function recordedBy(){
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
