<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'appointment_id',
        'doctor_id',
        'type',
        'title',
        'content',
        'previous_entry_id',
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

    public function previousEntry()
    {
        return $this->belongsTo(MedicalEntry::class, 'previous_entry_id');
    }

    public function attachments()
    {
        return $this->hasMany(MedicalAttachment::class);
    }

    public function vitalParameters()
    {
        return $this->hasMany(VitalParameter::class);
    }
}
