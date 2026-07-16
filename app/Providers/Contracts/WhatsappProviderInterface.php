<?php

namespace App\Providers\Contracts;

interface WhatsappProviderInterface
{
    public function send(string $phone, string $message);
}
