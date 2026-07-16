<?php

namespace App\Providers\Contracts;

interface SmsProviderInterface
{
    public function send(string $phone, string $message);
}
