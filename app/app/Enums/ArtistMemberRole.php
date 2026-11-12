<?php

namespace App\Enums;

enum ArtistMemberRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function canManage(): bool
    {
        return $this === self::Owner || $this === self::Admin;
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Proprietário',
            self::Admin => 'Admin',
            self::Member => 'Integrante',
        };
    }
}
