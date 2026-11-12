<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ApplicationStatus;
use App\Enums\ArtistKind;
use App\Enums\BookingStatus;
use App\Enums\OpportunityStatus;
use App\Enums\ProposalStatus;
use App\Models\Application;
use App\Models\ArtistProfile;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Follow;
use App\Models\Opportunity;
use App\Models\Proposal;
use App\Models\User;
use App\Models\VenueProfile;
use App\Services\CreateBookingFromProposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_apply_via_api_once(): void
    {
        [$venueOwner, $artistOwner, $opportunity, $artist] = $this->openGig();

        Sanctum::actingAs($artistOwner);

        $this->postJson("/api/v1/opportunities/{$opportunity->id}/applications", [
            'artist_profile_id' => $artist->id,
            'message' => 'Quero tocar.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'submitted');

        $this->postJson("/api/v1/opportunities/{$opportunity->id}/applications", [
            'artist_profile_id' => $artist->id,
        ])->assertStatus(422);

        Sanctum::actingAs($venueOwner);
        $this->getJson('/api/v1/applications')->assertOk();
    }

    public function test_venue_can_accept_application_via_api(): void
    {
        [$venueOwner, $artistOwner, $opportunity, $artist] = $this->openGig();

        $application = Application::query()->create([
            'opportunity_id' => $opportunity->id,
            'artist_profile_id' => $artist->id,
            'submitted_by_user_id' => $artistOwner->id,
            'status' => ApplicationStatus::Submitted,
        ]);

        Sanctum::actingAs($venueOwner);

        $this->patchJson("/api/v1/opportunities/{$opportunity->id}/applications/{$application->id}", [
            'status' => 'accepted',
        ])->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonStructure(['conversation_id']);

        $this->assertNotNull(Conversation::query()->where('application_id', $application->id)->first());
    }

    public function test_follow_artist_via_api(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();

        $artist = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Follow API',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        Sanctum::actingAs($fan);

        $this->postJson("/api/v1/artists/{$artist->slug}/follow")
            ->assertOk()
            ->assertJson(['following' => true]);

        $this->assertTrue(Follow::query()
            ->where('user_id', $fan->id)
            ->where('followable_id', $artist->id)
            ->exists());

        $this->deleteJson("/api/v1/artists/{$artist->slug}/follow")
            ->assertOk()
            ->assertJson(['following' => false]);
    }

    public function test_booking_list_and_cancel_via_api(): void
    {
        [$venueOwner, $artistOwner, $booking] = $this->confirmedBooking();

        Sanctum::actingAs($artistOwner);

        $this->getJson('/api/v1/bookings')
            ->assertOk()
            ->assertJsonFragment(['id' => $booking->id]);

        $this->getJson("/api/v1/bookings/{$booking->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->postJson("/api/v1/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Conflito de agenda',
        ])->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    /**
     * @return array{0: User, 1: User, 2: Opportunity, 3: ArtistProfile}
     */
    private function openGig(): array
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar API Market',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Gig API Market',
            'status' => OpportunityStatus::Open,
            'slots_count' => 2,
            'event_date' => now()->addDays(20)->toDateString(),
            'starts_at' => '21:00:00',
            'ends_at' => '23:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Artista API Market',
            'kind' => ArtistKind::Solo,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        return [$venueOwner, $artistOwner, $opportunity, $artist];
    }

    /**
     * @return array{0: User, 1: User, 2: Booking}
     */
    private function confirmedBooking(): array
    {
        [$venueOwner, $artistOwner, $opportunity, $artist] = $this->openGig();

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
        $this->assertSame(BookingStatus::Confirmed, $booking->status);

        return [$venueOwner, $artistOwner, $booking];
    }
}
