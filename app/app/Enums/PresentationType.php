<?php

namespace App\Enums;

enum PresentationType: string
{
    case Acoustic = 'acoustic';
    case Electric = 'electric';
    case DjSet = 'dj_set';
    case Tribute = 'tribute';
    case Original = 'original';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Acoustic => 'Acústico',
            self::Electric => 'Elétrico / banda completa',
            self::DjSet => 'Set de DJ',
            self::Tribute => 'Tribute',
            self::Original => 'Autoral',
            self::Mixed => 'Misto',
        };
    }
}
