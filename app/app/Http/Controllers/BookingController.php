<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\ArtistProfile;
use App\Models\Booking;
use App\Models\VenueProfile;
use App\Services\InAppNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Booking::class);

        $user = $request->user();

        $venueIds = VenueProfile::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('managers', fn ($q) => $q->where('user_id', $user->id));
            })
            ->pluck('id');

        $artistIds = ArtistProfile::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id));
            })
            ->get()
            ->filter(fn (ArtistProfile $artist) => $user->can('update', $artist))
            ->pluck('id');

        $bookings = Booking::query()
            ->where(function ($query) use ($venueIds, $artistIds) {
                $query->whereIn('venue_profile_id', $venueIds)
                    ->orWhereIn('artist_profile_id', $artistIds);
            })
            ->with(['opportunity', 'artistProfile', 'venueProfile'])
            ->orderBy('starts_at')
            ->paginate(20);

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load([
            'opportunity',
            'artistProfile',
            'venueProfile',
            'proposal',
            'conversation',
            'experience',
            'cancelledBy',
            'reviews',
        ]);

        $user = auth()->user();

        return view('bookings.show', [
            'booking' => $booking,
            'isArtistSide' => $user->can('update', $booking->artistProfile),
            'isVenueSide' => $user->can('manageOpportunities', $booking->venueProfile),
            'canReview' => $user ? $user->can('create', [\App\Models\Review::class, $booking]) : false,
        ]);
    }

    public function complete(Request $request, Booking $booking, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('complete', $booking);

        $booking->load(['artistProfile', 'venueProfile', 'opportunity']);
        $wasCompleted = $booking->status === BookingStatus::Completed;
        $booking->markCompletedBy($request->user());
        $booking->refresh();

        if (! $wasCompleted && $booking->status === BookingStatus::Completed) {
            $notifier->notifyMany(
                $notifier->venueManagerIds($booking->venueProfile)
                    ->merge($notifier->artistManagerIds($booking->artistProfile)),
                'booking.completed',
                'booking:'.$booking->id.':completed',
                'Show concluído',
                '“'.$booking->opportunity->title.'” foi marcado como concluído.',
                ['url' => route('bookings.show', $booking), 'booking_id' => $booking->id],
            );
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'booking-completion-recorded');
    }

    public function cancel(Request $request, Booking $booking, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->load(['artistProfile', 'venueProfile', 'opportunity']);

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $request->user()->id,
            'cancellation_reason' => $data['cancellation_reason'],
        ]);

        $recipients = $notifier->venueManagerIds($booking->venueProfile)
            ->merge($notifier->artistManagerIds($booking->artistProfile))
            ->reject(fn ($id) => (int) $id === (int) $request->user()->id)
            ->values();

        $notifier->notifyMany(
            $recipients,
            'booking.cancelled',
            'booking:'.$booking->id.':cancelled',
            'Booking cancelado',
            '“'.$booking->opportunity->title.'” foi cancelado: '.$data['cancellation_reason'],
            ['url' => route('bookings.show', $booking), 'booking_id' => $booking->id],
        );

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'booking-cancelled');
    }
}
