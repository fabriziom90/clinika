<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderTypePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'reminder_type_id',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function reminderType()
    {
        return $this->belongsTo(ReminderType::class);
    }
}
