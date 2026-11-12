<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;
use App\Models\VenueProfile;

class OpportunityPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Opportunity $opportunity): bool
    {
        if (in_array($opportunity->status->value, ['open', 'filled', 'closed'], true)) {
            return true;
        }

        return $user !== null && $user->can('manageOpportunities', $opportunity->venueProfile);
    }

    public function create(User $user, VenueProfile $venueProfile): bool
    {
        return $user->can('manageOpportunities', $venueProfile);
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $user->can('manageOpportunities', $opportunity->venueProfile);
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $this->update($user, $opportunity);
    }
}
