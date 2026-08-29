<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Patient extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'personal_code',
        'birthday',
        'birth_city',
        'city',
        'address',
        'phone',
        'email',
        'nationality_id',
        'genre',
        'zip_code',
    ];

    protected $casts = [
        'name' => 'encrypted',
        'surname' => 'encrypted',
        'personal_code' => 'encrypted',
        'birth_city' => 'encrypted',
        'city' => 'encrypted',
        'address' => 'encrypted',
        'phone' => 'encrypted',
        'email' => 'encrypted',
        'zip_code' => 'encrypted',

    ];

    protected static function booted()
    {
        static::created(function ($patient) {
            $patient->medicalRecord()->create();
        });
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function patientHistories()
    {
        return $this->hasMany(PatientHealthHistory::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function getNameForAuditAttribute()
    {
        return $this->name; // già decrypt grazie al cast
    }

    public function getSurnameForAuditAttribute()
    {
        return $this->surname; // già decrypt
    }

    public function reminderTypes()
    {
        return $this->belongsToMany(ReminderType::class, 'reminder_type_preferences');
    }

    public function appointmentReminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function consents()
    {
        return $this->hasMany(PatientConsent::class);
    }

    public function lastAppointment()
    {
        return $this->hasOne(Appointment::class)->where('start_time', '<=', now())->latestOfMany('start_time');
    }

    public function nextAppointment()
    {
        return $this->hasOne(Appointment::class)->where('start_time', '>=', now())->oldestOfMany('start_time');
    }

    public function transformAudit(array $data): array
    {
        $sensitive = [
            'personal_code', 'birthday', 'birth_city',
            'city', 'zip_code', 'address', 'phone', 'email', 'genre',
            'password', 'remember_token',
        ];

        foreach ($sensitive as $field) {
            unset($data['old_values'][$field], $data['new_values'][$field]);
        }

        // se vuoi, puoi mostrare name e surname decifrati tramite i getter
        if (isset($data['old_values']['name'])) {
            $data['old_values']['name'] = $this->getNameForAuditAttribute();
        }
        if (isset($data['new_values']['name'])) {
            $data['new_values']['name'] = $this->getNameForAuditAttribute();
        }
        if (isset($data['old_values']['surname'])) {
            $data['old_values']['surname'] = $this->getSurnameForAuditAttribute();
        }
        if (isset($data['new_values']['surname'])) {
            $data['new_values']['surname'] = $this->getSurnameForAuditAttribute();
        }

        return $data;
    }
}
