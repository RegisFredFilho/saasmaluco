<?php

namespace App\Support;

use App\Models\ArtistProfile;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Database\Eloquent\Model;

final class NetworkTarget
{
    public static function isOwn(User $user, Model $target): bool
    {
        if ($target instanceof ArtistProfile || $target instanceof VenueProfile) {
            return (int) $target->owner_user_id === (int) $user->id;
        }

        return false;
    }
}
