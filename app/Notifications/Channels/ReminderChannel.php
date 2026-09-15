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

        $message = $reminder->composedMessage();
        $startTime = Carbon::parse($appointment->start_time);

        return preg_replace(
            [
                '/\{\{\s*nome_cognome\s*\}\}/',
                '/\{\{\s*data_appuntamento\s*\}\}/',
                '/\{\{\s*orario_appuntamento\s*\}\}/',
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
