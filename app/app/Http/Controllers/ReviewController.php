<?php

namespace App\Http\Controllers;

use App\Enums\ReviewAuthorSide;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\VenueProfile;
use App\Policies\ReviewPolicy;
use App\Services\AdminAuditor;
use App\Services\InAppNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Booking $booking, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('create', [Review::class, $booking]);

        $booking->load(['artistProfile', 'venueProfile']);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var ReviewAuthorSide $side */
        $side = app(ReviewPolicy::class)->sideFor($request->user(), $booking);

        $rateable = $side === ReviewAuthorSide::Artist
            ? $booking->venueProfile
            : $booking->artistProfile;

        $review = Review::query()->create([
            'booking_id' => $booking->id,
            'author_user_id' => $request->user()->id,
            'author_side' => $side,
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        $recipients = $rateable instanceof VenueProfile
            ? $notifier->venueManagerIds($rateable)
            : $notifier->artistManagerIds($rateable);

        $profileUrl = $rateable instanceof VenueProfile
            ? route('venues.show', $rateable)
            : route('artists.show', $rateable);

        $notifier->notifyMany(
            $recipients,
            'review.created',
            'review:'.$review->id,
            'Nova avaliação',
            'Você recebeu uma avaliação '.$data['rating'].'/5.',
            ['url' => $profileUrl, 'review_id' => $review->id],
        );

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'review-created');
    }

    public function report(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('report', $review);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        ReviewReport::query()->firstOrCreate(
            [
                'review_id' => $review->id,
                'reporter_user_id' => $request->user()->id,
            ],
            ['reason' => $data['reason']],
        );

        return back()->with('status', 'review-reported');
    }

    public function hide(Request $request, Review $review, AdminAuditor $auditor): RedirectResponse
    {
        $this->authorize('hide', $review);

        $data = $request->validate([
            'hidden_reason' => ['required', 'string', 'max:500'],
        ]);

        $review->update([
            'is_hidden' => true,
            'hidden_reason' => $data['hidden_reason'],
            'hidden_by_user_id' => $request->user()->id,
            'hidden_at' => now(),
        ]);

        ReviewReport::query()
            ->where('review_id', $review->id)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => now(),
                'resolved_by_user_id' => $request->user()->id,
                'resolution' => 'hidden',
                'resolution_note' => $data['hidden_reason'],
            ]);

        $auditor->log($request->user(), 'review.hidden', $review, [
            'reason' => $data['hidden_reason'],
            'via' => 'direct',
        ]);

        return back()->with('status', 'review-hidden');
    }
}
