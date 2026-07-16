<?php

namespace App\Notifications\Channels;

use App\Models\AppointmentReminder;
use App\Providers\Contracts\EmailProviderInterface;

// use Illuminate\Support\Facades\Log;

class EmailReminderChannel extends ReminderChannel
{
    public function __construct(private EmailProviderInterface $provider) {}

    public function send(AppointmentReminder $reminder)
    {
        $message = $this->buildMessage($reminder);

        $this->provider->send(
            $reminder->patient->email,
            $reminder->reminderType->subject,
            $message
        );

    }
}
