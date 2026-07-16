<?php

namespace App\Notifications\Channels;

use App\Models\AppointmentReminder;
use Carbon\Carbon;

abstract class ReminderChannel
{
    protected function buildMessage(AppointmentReminder $reminder)
    {
        $appointment = $reminder->appointment;
        $patient = $reminder->patient;

        $message = $reminder->reminderType->message;
        $startTime = Carbon::parse($appointment->start_time);

        return str_replace(
            [
                '{{nome_cognome}}',
                '{{data_appuntamento}}',
                '{{orario_appuntamento}}',
            ],
            [
                $patient->name.' '.$patient->surname,
                $startTime->format('d/m/Y'),
                $startTime->format('H:i'),
            ],
            $message
        );
    }
}
