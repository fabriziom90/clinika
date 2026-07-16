<?php

namespace App\Providers\Log;

use App\Providers\Contracts\WhatsappProviderInterface;
use Illuminate\Support\Facades\Log;

class LogWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $phone, string $message)
    {
        Log::info('WHATSAPP_REMINDER', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
