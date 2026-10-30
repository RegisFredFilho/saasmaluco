<?php

namespace App\Policies;

use App\Models\ArtistProfile;
use App\Models\Post;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Database\Eloquent\Model;

class PostPolicy
{
    public function view(?User $user, Post $post): bool
    {
        if ($post->trashed()) {
            return false;
        }

        if ($post->is_public) {
            return $this->canViewPublisher($user, $post->publisher);
        }

        return $user !== null && $this->update($user, $post);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Post $post): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ((int) $post->author_user_id === (int) $user->id) {
            return $this->canPublishAs($user, $post->publisher);
        }

        return $this->canPublishAs($user, $post->publisher);
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    public function canPublishAs(User $user, ?Model $publisher): bool
    {
        if (! $user->isActive() || $publisher === null) {
            return false;
        }

        if ($publisher instanceof User) {
            return (int) $publisher->id === (int) $user->id;
        }

        if ($publisher instanceof ArtistProfile) {
            return $user->can('update', $publisher);
        }

        if ($publisher instanceof VenueProfile) {
            return $user->can('update', $publisher);
        }

        return false;
    }

    private function canViewPublisher(?User $user, ?Model $publisher): bool
    {
        if ($publisher instanceof ArtistProfile) {
            return $user ? $user->can('view', $publisher) : app(ArtistProfilePolicy::class)->view(null, $publisher);
        }

        if ($publisher instanceof VenueProfile) {
            return $user ? $user->can('view', $publisher) : app(VenueProfilePolicy::class)->view(null, $publisher);
        }

        return $publisher instanceof User;
    }
}
