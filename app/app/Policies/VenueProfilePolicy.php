<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VenueProfile;

class VenueProfilePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, VenueProfile $venueProfile): bool
    {
        if ($venueProfile->is_public) {
            return true;
        }

        return $user !== null && $this->manage($user, $venueProfile);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, VenueProfile $venueProfile): bool
    {
        return $this->manage($user, $venueProfile);
    }

    public function delete(User $user, VenueProfile $venueProfile): bool
    {
        return $user->isActive() && $venueProfile->owner_user_id === $user->id;
    }

    public function manageOpportunities(User $user, VenueProfile $venueProfile): bool
    {
        return $this->manage($user, $venueProfile);
    }

    public function manage(User $user, VenueProfile $venueProfile): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($venueProfile->owner_user_id === $user->id) {
            return true;
        }

        return $venueProfile->managers()
            ->where('user_id', $user->id)
            ->exists();
    }
}
