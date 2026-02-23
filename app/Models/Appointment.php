<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Appointment extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'doctor_id',
        'nurse_id',
        'patient_id',
        'title',
        'start_time',
        'duration',
        'notes',
    ];

    // protected $casts = [
    //     'start_time' => 'datetime:Y-m-d\TH:i:sP',
    //     'end_time' => 'datetime:Y-m-d\TH:i:sP',
    // ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function medicalEntry()
    {
        return $this->hasOne(MedicalEntry::class);
    }
}
