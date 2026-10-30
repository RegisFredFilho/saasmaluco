<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function respond(User $user, Proposal $proposal): bool
    {
        if (! $proposal->status->isRespondable() || ! $user->isActive()) {
            return false;
        }

        $conversation = $proposal->conversation;
        if (! $user->can('view', $conversation)) {
            return false;
        }

        $creatorIsVenue = $proposal->createdBy
            && $proposal->createdBy->can('manageOpportunities', $conversation->venueProfile);

        // Destinatário = lado oposto ao criador
        if ($creatorIsVenue) {
            return $user->can('update', $conversation->artistProfile);
        }

        return $user->can('manageOpportunities', $conversation->venueProfile);
    }
}
