<?php

namespace App\Enums;

enum VenueManagerRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';

    public function canManage(): bool
    {
        return true;
    }
}
