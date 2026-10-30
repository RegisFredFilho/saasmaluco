<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ArtistKind;
use App\Enums\BookingStatus;
use App\Enums\OpportunityStatus;
use App\Enums\ProposalStatus;
use App\Models\Application;
use App\Models\AppNotification;
use App\Models\ArtistProfile;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Opportunity;
use App\Models\Proposal;
use App\Models\User;
use App\Models\VenueProfile;
use App\Services\CreateBookingFromProposal;
use App\Services\InAppNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_submitted_notifies_venue_once(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar Notif',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Show notificado',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->addDays(10)->toDateString(),
            'starts_at' => '20:00:00',
            'ends_at' => '22:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Banda Notif',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $opportunity), [
            'artist_profile_id' => $artist->id,
            'message' => 'Quero tocar.',
        ])->assertRedirect();

        $this->assertEquals(1, AppNotification::query()
            ->where('user_id', $venueOwner->id)
            ->where('type', 'application.submitted')
            ->count());

        $this->assertEquals(0, AppNotification::query()
            ->where('user_id', $artistOwner->id)
            ->count());
    }

    public function test_idempotency_key_prevents_duplicates(): void
    {
        $user = User::factory()->create();
        $notifier = app(InAppNotifier::class);

        $first = $notifier->notify($user, 'test.ping', 'test:ping:1', 'Ping', 'Corpo');
        $second = $notifier->notify($user, 'test.ping', 'test:ping:1', 'Ping', 'Corpo');

        $this->assertNotNull($first);
        $this->assertEquals($first->id, $second?->id);
        $this->assertEquals(1, AppNotification::query()->where('user_id', $user->id)->count());
    }

    public function test_inbox_lists_own_notifications_and_mark_read_redirects(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $mine = AppNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'message.created',
            'title' => 'Nova mensagem',
            'body' => 'Olá',
            'data' => ['url' => route('dashboard')],
            'idempotency_key' => 'message:created:user:'.$user->id,
        ]);

        AppNotification::query()->create([
            'user_id' => $other->id,
            'type' => 'message.created',
            'title' => 'Outra',
            'body' => 'Segredo',
            'data' => null,
            'idempotency_key' => 'message:created:user:'.$other->id,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Nova mensagem')
            ->assertDontSee('Segredo');

        $this->actingAs($user)
            ->get(route('notifications.read', $mine))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($mine->fresh()->read_at);
    }

    public function test_accepting_proposal_notifies_booking_confirmed(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar Booking Notif',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Gig confirmada',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->addDays(15)->toDateString(),
            'starts_at' => '21:00:00',
            'ends_at' => '23:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Artista Booking Notif',
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
            'amount' => 1500,
            'currency' => 'BRL',
            'status' => ProposalStatus::Sent,
            'version' => 1,
        ]);

        $this->actingAs($artistOwner)->post(route('conversations.proposals.respond', [$conversation, $proposal]), [
            'decision' => 'accepted',
        ])->assertRedirect();

        $booking = Booking::query()->where('proposal_id', $proposal->id)->first();
        $this->assertNotNull($booking);

        $this->assertTrue(AppNotification::query()
            ->where('type', 'booking.confirmed')
            ->where('user_id', $venueOwner->id)
            ->exists());

        $this->assertTrue(AppNotification::query()
            ->where('type', 'booking.confirmed')
            ->where('user_id', $artistOwner->id)
            ->exists());
    }

    public function test_review_notifies_rateable_side(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar Review Notif',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Show review notif',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->subDays(2)->toDateString(),
            'starts_at' => '20:00:00',
            'ends_at' => '22:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Artista Review Notif',
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

        AppNotification::query()->delete();

        $this->actingAs($artistOwner)->post(route('bookings.reviews.store', $booking), [
            'rating' => 5,
            'comment' => 'Ótimo espaço.',
        ])->assertRedirect();

        $this->assertTrue(AppNotification::query()
            ->where('user_id', $venueOwner->id)
            ->where('type', 'review.created')
            ->exists());
    }
}
