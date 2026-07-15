<?php

namespace App\Services;

use App\Enums\ReminderStatus;
use App\Models\AppointmentReminder;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReminderSenderService
{
    public function send(AppointmentReminder $reminder)
    {
        try {
            switch ($reminder->reminderType->code) {
                case 'email':
                    $this->sendEmail($reminder);
                    break;
                case 'sms':
                    $this->sendSms($reminder);
                    break;
                case 'whatsapp':
                    $this->sendWhatsapp($reminder);
                    break;

                default:
                    throw new \Exception('Tipologia di reminder non supportata.');
            }

            $reminder->update([
                'status' => ReminderStatus::SENT,
                'sent_at' => now(),
                'error_message' => null,
            ]);

            return true;

        } catch (Throwable $e) {

            $reminder->update([
                'status' => ReminderStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error($e);

            return false;
        }
    }

    private function sendEmail(AppointmentReminder $reminder): void
    {
        Log::info('EMAIL REMINDER', [
            'appointment' => $reminder->appointment_id,
            'patient' => $reminder->patient_id,
        ]);

    }

    private function sendSms(AppointmentReminder $reminder): void
    {
        Log::info('SMS REMINDER', [
            'appointment' => $reminder->appointment_id,
            'patient' => $reminder->patient_id,
        ]);

    }

    private function sendWhatsapp(AppointmentReminder $reminder): void
    {
        Log::info('WHATSAPP REMINDER', [
            'appointment' => $reminder->appointment_id,
            'patient' => $reminder->patient_id,
        ]);

    }
}
