<?php

namespace App\Enums;

enum ArtistInviteStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Cancelled = 'cancelled';

    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
