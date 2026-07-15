<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Carbon\Carbon;

class AppointmentReminderService
{
    public function generate(Appointment $appointment)
    {
        $patient = $appointment->patient;

        if (! $patient) {
            return;
        }

        $patientReminders = $patient->reminderTypes()->where('active', true)->get();

        if ($patientReminders->isEmpty()) {
            return;
        }

        foreach ($patientReminders as $reminder) {
            AppointmentReminder::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $patient->id,
                'reminder_type_id' => $reminder->id,
                'scheduled_for' => $this->calculateScheduledDate($appointment, $reminder),
                'status' => 'pending',
            ]);
        }

    }

    public function regenerate(Appointment $appointment)
    {
        AppointmentReminder::where('appointment_id', $appointment->id)->where('status', 'pending')->delete();

        $this->generate($appointment);
    }

    private function calculateScheduledDate(Appointment $appointment, $reminderType)
    {
        $appointmentDate = Carbon::parse($appointment->start_time);

        if ($reminderType->sent_before_unit === 'days') {
            $date = $appointmentDate->subDays($reminderType->sent_before_value);
        } elseif ($reminderType->sent_before_unit === 'hours') {
            $date = $appointmentDate->subHours($reminderType->sent_before_value);
        } else {
            $date = $appointmentDate;
        }

        // return $date->setTime(8, 0);
        return now(); // only for testing

    }
}
