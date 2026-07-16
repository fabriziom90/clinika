<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Provider reminder
    |--------------------------------------------------------------------------
    |
    | Possibili valori:
    | log
    | twilio
    |
    */

    'email_provider' => env('REMINDER_EMAIL_PROVIDER', 'log'),

    'sms_provider' => env('REMINDER_SMS_PROVIDER', 'log'),

    'whatsapp_provider' => env('REMINDER_WHATSAPP_PROVIDER', 'log'),

];
