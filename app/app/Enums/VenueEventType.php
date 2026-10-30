<?php

namespace App\Enums;

enum VenueEventType: string
{
    case LiveMusic = 'live_music';
    case DjNight = 'dj_night';
    case PrivateParty = 'private_party';
    case Festival = 'festival';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LiveMusic => 'Música ao vivo',
            self::DjNight => 'Noite de DJ',
            self::PrivateParty => 'Evento privado',
            self::Festival => 'Festival',
            self::Other => 'Outro',
        };
    }
}
