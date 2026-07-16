<?php

namespace App\Providers\Log;

use App\Providers\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $phone, string $message)
    {
        Log::info('SMS_REMINDER', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
