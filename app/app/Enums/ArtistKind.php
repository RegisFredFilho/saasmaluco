<?php

namespace App\Enums;

enum ArtistKind: string
{
    case Solo = 'solo';
    case Band = 'band';
    case Dj = 'dj';

    public function label(): string
    {
        return match ($this) {
            self::Solo => 'Artista solo',
            self::Band => 'Banda / grupo',
            self::Dj => 'DJ',
        };
    }
}
