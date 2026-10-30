<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class NegotiationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: User, 2: Conversation, 3: Opportunity}
     */
    private function acceptedNegotiation(): array
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar Negócio',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Gig negociada',
            'status' => OpportunityStatus::Open,
            'slots_count' => 2,
            'event_date' => now()->addDays(20)->toDateString(),
            'starts_at' => '21:00:00',
            'ends_at' => '23:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Banda Negócio',
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

        return [$venueOwner, $artistOwner, $conversation, $opportunity];
    }

    public function test_participants_can_exchange_messages_stranger_cannot(): void
    {
        [$venueOwner, $artistOwner, $conversation] = $this->acceptedNegotiation();
        $stranger = User::factory()->create();

        $this->actingAs($artistOwner)->post(route('conversations.messages.store', $conversation), [
            'body' => 'Olá, podemos alinhar o horário?',
        ])->assertRedirect(route('conversations.show', $conversation));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $artistOwner->id,
            'body' => 'Olá, podemos alinhar o horário?',
        ]);

        $this->actingAs($stranger)
            ->get(route('conversations.show', $conversation))
            ->assertForbidden();

        $this->actingAs($stranger)->post(route('conversations.messages.store', $conversation), [
            'body' => 'Intruso',
        ])->assertForbidden();
    }

    public function test_proposal_versioning_and_only_recipient_can_accept(): void
    {
        [$venueOwner, $artistOwner, $conversation] = $this->acceptedNegotiation();

        $this->actingAs($venueOwner)->post(route('conversations.proposals.store', $conversation), [
            'amount' => 1500,
            'conditions' => 'Som incluso',
        ])->assertRedirect();

        $first = Proposal::query()->first();
        $this->assertSame(ProposalStatus::Sent, $first->status);
        $this->assertSame(1, $first->version);

        $this->actingAs($venueOwner)->post(route('conversations.proposals.store', $conversation), [
            'amount' => 1800,
            'conditions' => 'Som e luz',
        ])->assertRedirect();

        $first->refresh();
        $second = Proposal::query()->orderByDesc('id')->first();
        $this->assertSame(ProposalStatus::Superseded, $first->status);
        $this->assertSame(ProposalStatus::Sent, $second->status);
        $this->assertSame(2, $second->version);
        $this->assertSame($second->id, $first->superseded_by_id);

        $this->actingAs($venueOwner)->post(
            route('conversations.proposals.respond', [$conversation, $second]),
            ['decision' => 'accepted']
        )->assertForbidden();

        $accept = $this->actingAs($artistOwner)->post(
            route('conversations.proposals.respond', [$conversation, $second]),
            ['decision' => 'accepted']
        );

        $this->assertSame(ProposalStatus::Accepted, $second->fresh()->status);
        $this->assertNotNull($second->fresh()->booking);
        $accept->assertRedirect(route('bookings.show', $second->fresh()->booking));

        $this->actingAs($venueOwner)->post(route('conversations.proposals.store', $conversation), [
            'amount' => 2000,
        ])->assertSessionHasErrors('amount');
    }

    public function test_messages_are_paginated(): void
    {
        [$venueOwner, $artistOwner, $conversation] = $this->acceptedNegotiation();

        for ($i = 1; $i <= 31; $i++) {
            $conversation->messages()->create([
                'user_id' => $artistOwner->id,
                'body' => "Mensagem {$i}",
            ]);
        }

        $this->actingAs($venueOwner)
            ->get(route('conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Mensagem 1')
            ->assertDontSee('Mensagem 31');

        $this->actingAs($venueOwner)
            ->get(route('conversations.show', ['conversation' => $conversation, 'messages_page' => 2]))
            ->assertOk()
            ->assertSee('Mensagem 31');
    }
}
