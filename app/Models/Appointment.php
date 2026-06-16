<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
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
        'status'
    ];

    // protected $casts = [
    //     'start_time' => 'datetime:Y-m-d\TH:i:sP',
    //     'end_time' => 'datetime:Y-m-d\TH:i:sP',
    // ];

    protected $appends = ['status_label'];

    protected $casts = [
        "status" => AppointmentStatus::class
    ];

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

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status?->label() ?? "scheduled";
    }
}
