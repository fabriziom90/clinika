<?php

namespace App\Providers\Log;

use App\Providers\Contracts\EmailProviderInterface;
use Illuminate\Support\Facades\Log;

class LogEmailProvider implements EmailProviderInterface
{
    public function send(string $to, string $subject, string $message)
    {
        Log::info('EMAIL_REMINDER', [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
        ]);
    }
}
