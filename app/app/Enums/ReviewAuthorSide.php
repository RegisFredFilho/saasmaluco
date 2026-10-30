<?php

namespace App\Enums;

enum ReviewAuthorSide: string
{
    case Artist = 'artist';
    case Venue = 'venue';

    public function label(): string
    {
        return match ($this) {
            self::Artist => 'Artista',
            self::Venue => 'Estabelecimento',
        };
    }
}
