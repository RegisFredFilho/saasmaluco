<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Opportunity;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Application $application): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->can('manageOpportunities', $application->opportunity->venueProfile)) {
            return true;
        }

        return $user->can('update', $application->artistProfile);
    }

    public function create(User $user, Opportunity $opportunity): bool
    {
        return $user->isActive() && $opportunity->acceptsNewApplications();
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $user->can('manageOpportunities', $application->opportunity->venueProfile);
    }

    public function withdraw(User $user, Application $application): bool
    {
        return $user->isActive()
            && $application->status->canBeWithdrawnByArtist()
            && $user->can('update', $application->artistProfile);
    }
}
