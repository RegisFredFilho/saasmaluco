<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $this->participate($user, $conversation);
    }

    public function message(User $user, Conversation $conversation): bool
    {
        return $this->participate($user, $conversation);
    }

    public function propose(User $user, Conversation $conversation): bool
    {
        return $this->participate($user, $conversation);
    }

    public function participate(User $user, Conversation $conversation): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->can('manageOpportunities', $conversation->venueProfile)
            || $user->can('update', $conversation->artistProfile);
    }

    public function isVenueSide(User $user, Conversation $conversation): bool
    {
        return $user->can('manageOpportunities', $conversation->venueProfile);
    }

    public function isArtistSide(User $user, Conversation $conversation): bool
    {
        return $user->can('update', $conversation->artistProfile);
    }
}
