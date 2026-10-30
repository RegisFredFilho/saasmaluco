<?php

namespace App\Policies;

use App\Models\ArtistProfile;
use App\Models\User;

class ArtistProfilePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ArtistProfile $artistProfile): bool
    {
        if ($artistProfile->is_public) {
            return true;
        }

        return $user !== null && $this->manage($user, $artistProfile);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, ArtistProfile $artistProfile): bool
    {
        return $this->manage($user, $artistProfile);
    }

    public function delete(User $user, ArtistProfile $artistProfile): bool
    {
        return $artistProfile->owner_user_id === $user->id && $user->isActive();
    }

    public function manageMedia(User $user, ArtistProfile $artistProfile): bool
    {
        return $this->manage($user, $artistProfile);
    }

    public function manageMembers(User $user, ArtistProfile $artistProfile): bool
    {
        return $this->manage($user, $artistProfile);
    }

    public function manage(User $user, ArtistProfile $artistProfile): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($artistProfile->owner_user_id === $user->id) {
            return true;
        }

        $membership = $artistProfile->members()
            ->where('user_id', $user->id)
            ->first();

        return $membership !== null && $membership->role->canManage();
    }
}
