<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ArtistKind;
use App\Enums\BookingStatus;
use App\Enums\OpportunityStatus;
use App\Enums\ProposalStatus;
use App\Enums\ReviewAuthorSide;
use App\Models\Application;
use App\Models\ArtistProfile;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Opportunity;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\User;
use App\Models\VenueProfile;
use App\Services\CreateBookingFromProposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: User, 2: Booking}
     */
    private function completedBooking(): array
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar Review',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Show avaliado',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->subDays(2)->toDateString(),
            'starts_at' => '20:00:00',
            'ends_at' => '22:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Artista Review',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $application = Application::query()->create([
            'opportunity_id' => $opportunity->id,
            'artist_profile_id' => $artist->id,
            'submitted_by_user_id' => $artistOwner->id,
            'status' => ApplicationStatus::Accepted,
        ]);

        $conversation = Conversation::openForApplication($application);

        $proposal = Proposal::query()->create([
            'conversation_id' => $conversation->id,
            'created_by_user_id' => $venueOwner->id,
            'amount' => 1000,
            'currency' => 'BRL',
            'status' => ProposalStatus::Accepted,
            'version' => 1,
            'responded_at' => now(),
        ]);

        $booking = app(CreateBookingFromProposal::class)->handle($proposal);
        $booking->update([
            'status' => BookingStatus::Completed,
            'artist_completed_at' => now()->subDay(),
            'venue_completed_at' => now()->subDay(),
            'completed_at' => now()->subDay(),
        ]);

        return [$venueOwner, $artistOwner, $booking->fresh()];
    }

    public function test_participants_can_review_independently_once(): void
    {
        [$venueOwner, $artistOwner, $booking] = $this->completedBooking();

        $this->actingAs($artistOwner)->post(route('bookings.reviews.store', $booking), [
            'rating' => 5,
            'comment' => 'Casa excelente.',
        ])->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'author_side' => ReviewAuthorSide::Artist->value,
            'rating' => 5,
            'rateable_type' => VenueProfile::class,
        ]);

        $this->actingAs($artistOwner)->post(route('bookings.reviews.store', $booking), [
            'rating' => 4,
        ])->assertForbidden();

        $this->actingAs($venueOwner)->post(route('bookings.reviews.store', $booking), [
            'rating' => 4,
            'comment' => 'Banda pontual.',
        ])->assertRedirect();

        $this->assertSame(2, Review::query()->where('booking_id', $booking->id)->count());

        $this->get(route('venues.show', $booking->venueProfile))
            ->assertOk()
            ->assertSee('Casa excelente.')
            ->assertSee('5,0');

        $this->get(route('artists.show', $booking->artistProfile))
            ->assertOk()
            ->assertSee('Banda pontual.');
    }

    public function test_stranger_cannot_review_and_window_enforced(): void
    {
        [$venueOwner, , $booking] = $this->completedBooking();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->post(route('bookings.reviews.store', $booking), [
            'rating' => 1,
        ])->assertForbidden();

        $booking->update(['completed_at' => now()->subDays(Review::WINDOW_DAYS + 1)]);

        $this->actingAs($venueOwner)->post(route('bookings.reviews.store', $booking), [
            'rating' => 3,
        ])->assertForbidden();
    }

    public function test_report_and_admin_hide(): void
    {
        [$venueOwner, $artistOwner, $booking] = $this->completedBooking();
        $admin = User::factory()->create(['is_platform_admin' => true]);

        $this->actingAs($artistOwner)->post(route('bookings.reviews.store', $booking), [
            'rating' => 2,
            'comment' => 'Conteúdo questionável',
        ]);

        $review = Review::query()->first();

        $this->actingAs($venueOwner)->post(route('reviews.report', $review), [
            'reason' => 'Linguagem abusiva',
        ])->assertRedirect();

        $this->assertDatabaseHas('review_reports', [
            'review_id' => $review->id,
            'reporter_user_id' => $venueOwner->id,
        ]);

        $this->actingAs($artistOwner)->post(route('reviews.hide', $review), [
            'hidden_reason' => 'Tentativa sem admin',
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('reviews.hide', $review), [
            'hidden_reason' => 'Moderação',
        ])->assertRedirect();

        $this->assertTrue($review->fresh()->is_hidden);

        $this->get(route('venues.show', $booking->venueProfile))
            ->assertOk()
            ->assertDontSee('Conteúdo questionável');
    }
}
