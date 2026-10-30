<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Enums\ReviewAuthorSide;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Booking $booking): bool
    {
        if (! $user->isActive() || $booking->status !== BookingStatus::Completed) {
            return false;
        }

        if ($booking->completed_at === null
            || $booking->completed_at->lt(now()->subDays(Review::WINDOW_DAYS))) {
            return false;
        }

        $side = $this->sideFor($user, $booking);
        if ($side === null) {
            return false;
        }

        return ! $booking->reviews()->where('author_side', $side)->exists();
    }

    public function report(User $user, Review $review): bool
    {
        return $user->isActive()
            && ! $review->is_hidden
            && (int) $review->author_user_id !== (int) $user->id;
    }

    public function hide(User $user, Review $review): bool
    {
        return $user->isActive() && $user->isPlatformAdmin();
    }

    public function sideFor(User $user, Booking $booking): ?ReviewAuthorSide
    {
        if ($user->can('update', $booking->artistProfile)) {
            return ReviewAuthorSide::Artist;
        }

        if ($user->can('manageOpportunities', $booking->venueProfile)) {
            return ReviewAuthorSide::Venue;
        }

        return null;
    }
}
