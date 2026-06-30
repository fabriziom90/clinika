<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MedicalEntry extends Model implements AuditableContract
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'appointment_id',
        'doctor_id',
        'cancelled_by',
        'cancelled_at',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function versions()
    {
        return $this->hasMany(MedicalEntryVersion::class)->orderByDesc('version');
    }

    public function latestVersion()
    {
        return $this->hasOne(MedicalEntryVersion::class)->latestOfMany();
    }

    public function latestActiveVersion()
    {
        return $this->hasOne(MedicalEntryVersion::class)
            ->whereNull('voided_at')
            ->orderByDesc('version');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(Doctor::class, 'cancelled_by');
    }

    public function latestAttachments()
    {
        return $this->latestVersion ? $this->latestVersion->attachments : collect();
    }

    public function latestPrescriptions()
    {
        return $this->latestVersion ? $this->latestVersion->prescriptions : collect();
    }

    public function latestVitalParameters()
    {
        return $this->latestVersion ? $this->latestVersion->vitalParameters : null;
    }
}
