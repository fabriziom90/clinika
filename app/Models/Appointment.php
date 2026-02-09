<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

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
}
