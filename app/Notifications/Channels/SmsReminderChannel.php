<?php

namespace App\Notifications\Channels;

use App\Models\AppointmentReminder;
use App\Providers\Contracts\SmsProviderInterface;

// use Illuminate\Support\Facades\Log;

class SmsReminderChannel extends ReminderChannel
{
    public function __construct(private SmsProviderInterface $provider) {}

    public function send(AppointmentReminder $reminder)
    {
        $message = $this->buildMessage($reminder);

        $this->provider->send(
            $reminder->patient->phone,
            $message
        );
    }
}
