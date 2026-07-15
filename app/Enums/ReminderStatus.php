<?php

namespace App\Enums;

enum ReminderStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
}
