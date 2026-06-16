<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string{
        return match ($this) {
             self::Scheduled => "Prenotato" ,
             self::InProgress => "In corso",
             self::Completed => "Completato",
             self::Cancelled => "Cancellato",
             self::NoShow => "Assente"
        };
    }
}
