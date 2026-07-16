<?php

namespace App\Notifications\Channels;

use App\Models\AppointmentReminder;
use App\Providers\Contracts\WhatsappProviderInterface;

// use Illuminate\Support\Facades\Log;

class WhatsappReminderChannel extends ReminderChannel
{
    public function __construct(private WhatsappProviderInterface $provider) {}

    public function send(AppointmentReminder $reminder)
    {

        $message = $this->buildMessage($reminder);

        $this->provider->send(
            $reminder->patient->phone,
            $message
        );
    }
}
