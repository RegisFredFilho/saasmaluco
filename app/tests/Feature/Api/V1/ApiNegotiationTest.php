<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ApplicationStatus;
use App\Enums\ArtistKind;
use App\Enums\OpportunityStatus;
use App\Enums\ProposalStatus;
use App\Models\Application;
use App\Models\ArtistProfile;
use App\Models\Conversation;
use App\Models\Opportunity;
use App\Models\Proposal;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiNegotiationTest extends TestCase
{
    use RefreshDatabase;

    public function test_participants_can_message_and_stranger_cannot(): void
    {
        [$venueOwner, $artistOwner, $conversation] = $this->acceptedNegotiation();
        $stranger = User::factory()->create();

        Sanctum::actingAs($artistOwner);
        $this->postJson("/api/v1/conversations/{$conversation->id}/messages", [
            'body' => 'Alinhamos o som?',
        ])->assertCreated()
            ->assertJsonPath('data.body', 'Alinhamos o som?');

        Sanctum::actingAs($stranger);
        $this->getJson("/api/v1/conversations/{$conversation->id}")
            ->assertForbidden();
        $this->postJson("/api/v1/conversations/{$conversation->id}/messages", [
            'body' => 'Intruso',
        ])->assertForbidden();
    }

    public function test_proposal_versioning_and_accept_creates_booking(): void
    {
        [$venueOwner, $artistOwner, $conversation] = $this->acceptedNegotiation();

        Sanctum::actingAs($venueOwner);
        $this->postJson("/api/v1/conversations/{$conversation->id}/proposals", [
            'amount' => 1500,
            'conditions' => 'Som incluso',
        ])->assertCreated()
            ->assertJsonPath('data.version', 1)
            ->assertJsonPath('data.status', 'sent');

        $this->postJson("/api/v1/conversations/{$conversation->id}/proposals", [
            'amount' => 1800,
        ])->assertCreated()
            ->assertJsonPath('data.version', 2);

        $first = Proposal::query()->orderBy('id')->first();
        $second = Proposal::query()->orderByDesc('id')->first();
        $this->assertSame(ProposalStatus::Superseded, $first->status);
        $this->assertSame(ProposalStatus::Sent, $second->status);

        $this->postJson("/api/v1/conversations/{$conversation->id}/proposals/{$second->id}/respond", [
            'decision' => 'accepted',
        ])->assertForbidden();

        Sanctum::actingAs($artistOwner);
        $this->postJson("/api/v1/conversations/{$conversation->id}/proposals/{$second->id}/respond", [
            'decision' => 'accepted',
        ])->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonStructure(['booking' => ['id', 'status']]);

        $this->assertSame(ProposalStatus::Accepted, $second->fresh()->status);
        $this->assertNotNull($second->fresh()->booking);

        Sanctum::actingAs($venueOwner);
        $this->getJson('/api/v1/conversations')
            ->assertOk()
            ->assertJsonFragment(['id' => $conversation->id]);

        $this->getJson("/api/v1/conversations/{$conversation->id}")
            ->assertOk()
            ->assertJsonPath('meta.has_accepted_proposal', true)
            ->assertJsonPath('meta.can_propose', false);
    }

    /**
     * @return array{0: User, 1: User, 2: Conversation}
     */
    private function acceptedNegotiation(): array
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar API Negócio',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Gig API negociada',
            'status' => OpportunityStatus::Open,
            'slots_count' => 2,
            'event_date' => now()->addDays(20)->toDateString(),
            'starts_at' => '21:00:00',
            'ends_at' => '23:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Banda API Negócio',
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

        return [$venueOwner, $artistOwner, $conversation];
    }
}
