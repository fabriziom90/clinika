<?php

namespace App\Services;

use App\Enums\ReminderStatus;
use App\Models\AppointmentReminder;
use App\Notifications\Channels\EmailReminderChannel;
use App\Notifications\Channels\SmsReminderChannel;
use App\Notifications\Channels\WhatsappReminderChannel;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReminderSenderService
{
    public function send(AppointmentReminder $reminder)
    {
        try {
            $reminder->update([
                'attempts' => $reminder->attempts + 1,
                'last_attempt_at' => now(),
            ]);

            $channel = match ($reminder->reminderType->code) {
                'email' => app(EmailReminderChannel::class),
                'sms' => app(SmsReminderChannel::class),
                'whatsapp' => app(WhatsappReminderChannel::class),
                default => throw new \Exception('Canale reminder non supportato.')
            };

            $channel->send($reminder);

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
}
