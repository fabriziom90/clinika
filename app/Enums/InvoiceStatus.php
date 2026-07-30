<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bozza' ,
            self::Issued => 'Emessa',
            self::Completed => 'Saldata',
            self::Cancelled => 'Cancellata',
        };
    }
}
