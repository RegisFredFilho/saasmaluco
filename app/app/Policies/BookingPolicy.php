<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Booking $booking): bool
    {
        return $this->participate($user, $booking);
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $booking->status === BookingStatus::Confirmed
            && $this->participate($user, $booking);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $booking->status === BookingStatus::Confirmed
            && $this->participate($user, $booking);
    }

    public function participate(User $user, Booking $booking): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->can('manageOpportunities', $booking->venueProfile)
            || $user->can('update', $booking->artistProfile);
    }
}
