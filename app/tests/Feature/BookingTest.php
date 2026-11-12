<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ArtistKind;
use App\Enums\BookingStatus;
use App\Enums\OpportunityStatus;
use App\Enums\ProposalStatus;
use App\Models\Application;
use App\Models\ArtistExperience;
use App\Models\ArtistProfile;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Opportunity;
use App\Models\Proposal;
use App\Models\User;
use App\Models\VenueProfile;
use App\Services\CreateBookingFromProposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: User, 2: Booking}
     */
    private function confirmedBooking(array $opportunityOverrides = []): array
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Casa Agenda',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create(array_merge([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Noite booking',
            'status' => OpportunityStatus::Open,
            'slots_count' => 2,
            'event_date' => now()->addDays(15)->toDateString(),
            'starts_at' => '20:00:00',
            'ends_at' => '22:00:00',
        ], $opportunityOverrides));

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Artista Agenda',
            'kind' => ArtistKind::Solo,
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
            'amount' => 1200,
            'currency' => 'BRL',
            'status' => ProposalStatus::Accepted,
            'version' => 1,
            'responded_at' => now(),
        ]);

        $booking = app(CreateBookingFromProposal::class)->handle($proposal);

        return [$venueOwner, $artistOwner, $booking];
    }

    public function test_dual_completion_creates_verified_experience(): void
    {
        [$venueOwner, $artistOwner, $booking] = $this->confirmedBooking();

        $this->actingAs($artistOwner)
            ->post(route('bookings.complete', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertNotNull($booking->artist_completed_at);
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(0, ArtistExperience::query()->where('booking_id', $booking->id)->count());

        $this->actingAs($venueOwner)
            ->post(route('bookings.complete', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);

        $experience = ArtistExperience::query()->where('booking_id', $booking->id)->first();
        $this->assertNotNull($experience);
        $this->assertTrue($experience->is_verified);
        $this->assertSame('booking', $experience->source);
    }

    public function test_conflicting_booking_is_rejected(): void
    {
        [$venueOwner, $artistOwner, $booking] = $this->confirmedBooking();

        $venue2 = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Outro Bar',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity2 = Opportunity::query()->create([
            'venue_profile_id' => $venue2->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Conflito',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => $booking->starts_at->toDateString(),
            'starts_at' => '21:00:00',
            'ends_at' => '23:00:00',
        ]);

        $application2 = Application::query()->create([
            'opportunity_id' => $opportunity2->id,
            'artist_profile_id' => $booking->artist_profile_id,
            'submitted_by_user_id' => $artistOwner->id,
            'status' => ApplicationStatus::Accepted,
        ]);

        $conversation2 = Conversation::openForApplication($application2);

        $proposal2 = Proposal::query()->create([
            'conversation_id' => $conversation2->id,
            'created_by_user_id' => $venueOwner->id,
            'amount' => 900,
            'currency' => 'BRL',
            'status' => ProposalStatus::Sent,
            'version' => 1,
        ]);

        $this->actingAs($artistOwner)->post(
            route('conversations.proposals.respond', [$conversation2, $proposal2]),
            ['decision' => 'accepted']
        )->assertSessionHasErrors('decision');

        $this->assertSame(ProposalStatus::Sent, $proposal2->fresh()->status);
        $this->assertSame(1, Booking::query()->count());
    }

    public function test_cancel_requires_reason(): void
    {
        [$venueOwner, , $booking] = $this->confirmedBooking();

        $this->actingAs($venueOwner)
            ->post(route('bookings.cancel', $booking), [])
            ->assertSessionHasErrors('cancellation_reason');

        $this->actingAs($venueOwner)
            ->post(route('bookings.cancel', $booking), [
                'cancellation_reason' => 'Chuva no outdoor',
            ])
            ->assertRedirect();

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertSame($venueOwner->id, $booking->fresh()->cancelled_by_user_id);
    }
}
