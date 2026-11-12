<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\ArtistProfile;
use App\Models\Booking;
use App\Models\VenueProfile;
use App\Services\InAppNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
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

        return BookingResource::collection($bookings);
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        $booking->load(['opportunity', 'artistProfile', 'venueProfile']);

        return BookingResource::make($booking);
    }

    public function complete(Request $request, Booking $booking, InAppNotifier $notifier): BookingResource
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

        return BookingResource::make($booking->load(['opportunity', 'artistProfile', 'venueProfile']));
    }

    public function cancel(Request $request, Booking $booking, InAppNotifier $notifier): BookingResource
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

        return BookingResource::make($booking->fresh()->load(['opportunity', 'artistProfile', 'venueProfile']));
    }
}
