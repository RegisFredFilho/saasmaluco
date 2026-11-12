<?php

namespace App\Policies;

use App\Enums\ArtistInviteStatus;
use App\Models\ArtistMemberInvite;
use App\Models\User;

class ArtistMemberInvitePolicy
{
    public function accept(User $user, ArtistMemberInvite $invite): bool
    {
        return $user->isActive()
            && $invite->status === ArtistInviteStatus::Pending
            && (int) $invite->invited_user_id === (int) $user->id;
    }

    public function decline(User $user, ArtistMemberInvite $invite): bool
    {
        return $this->accept($user, $invite);
    }

    public function cancel(User $user, ArtistMemberInvite $invite): bool
    {
        return $invite->status === ArtistInviteStatus::Pending
            && $user->can('manageMembers', $invite->artistProfile);
    }
}
