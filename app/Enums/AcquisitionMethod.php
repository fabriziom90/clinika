<?php

namespace App\Enums;

enum AcquisitionMethod: string
{
    case Paper = 'Cartaceo';
    case Upload = 'Upload';
    case ElectronicSignature = 'Firma Elettronica';

    public function label(): string{
        return match ($this) {
             self::Paper => "Cartaceo" ,
             self::Upload => "Upload",
             self::ElectronicSignature => "FirmaElettronica",
        };
    }
}
