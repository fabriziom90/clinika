<?php

namespace App\Providers\Contracts;

interface EmailProviderInterface
{
    public function send(string $to, string $subject, string $message);
}
