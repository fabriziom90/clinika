<?php

namespace App\Models;

use App\Enums\ReminderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id', 'patient_id', 'reminder_type_id', 'scheduled_for', 'sent_at', 'status', 'error_message',
    ];

    protected $casts = [
        'status' => ReminderStatus::class,
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function reminderType()
    {
        return $this->belongsTo(ReminderType::class);
    }
}
