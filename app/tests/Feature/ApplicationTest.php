<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ArtistKind;
use App\Enums\OpportunityStatus;
use App\Models\Application;
use App\Models\ArtistProfile;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function openOpportunity(User $owner, array $overrides = []): Opportunity
    {
        $venue = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Casa Gig',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        return Opportunity::query()->create(array_merge([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Sexta ao vivo',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->addDays(14)->toDateString(),
        ], $overrides));
    }

    public function test_artist_can_apply_once_and_venue_can_accept(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();
        $opportunity = $this->openOpportunity($venueOwner);
        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Duo Teste',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $opportunity), [
            'artist_profile_id' => $artist->id,
            'message' => 'Queremos tocar.',
        ])->assertRedirect(route('opportunities.show', $opportunity));

        $this->assertDatabaseHas('applications', [
            'opportunity_id' => $opportunity->id,
            'artist_profile_id' => $artist->id,
            'status' => ApplicationStatus::Submitted->value,
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $opportunity), [
            'artist_profile_id' => $artist->id,
        ])->assertSessionHasErrors('artist_profile_id');

        $application = Application::query()->first();

        $response = $this->actingAs($venueOwner)->patch(
            route('opportunities.applications.status', [$opportunity, $application]),
            ['status' => ApplicationStatus::Accepted->value]
        );

        $this->assertSame(ApplicationStatus::Accepted, $application->fresh()->status);
        $this->assertSame(OpportunityStatus::Filled, $opportunity->fresh()->status);
        $this->assertNotNull($application->fresh()->conversation);
        $response->assertRedirect(route('conversations.show', $application->fresh()->conversation));
    }

    public function test_closed_or_past_deadline_rejects_application(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();
        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Solo Late',
            'kind' => ArtistKind::Solo,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $closed = $this->openOpportunity($venueOwner, [
            'title' => 'Já fechada',
            'status' => OpportunityStatus::Closed,
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $closed), [
            'artist_profile_id' => $artist->id,
        ])->assertForbidden();

        $expired = $this->openOpportunity($venueOwner, [
            'title' => 'Prazo passou',
            'application_deadline' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $expired), [
            'artist_profile_id' => $artist->id,
        ])->assertForbidden();
    }

    public function test_artist_can_withdraw_while_pending(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();
        $opportunity = $this->openOpportunity($venueOwner);
        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Retirada Band',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $opportunity), [
            'artist_profile_id' => $artist->id,
        ]);

        $application = Application::query()->first();

        $this->actingAs($artistOwner)
            ->delete(route('opportunities.applications.withdraw', [$opportunity, $application]))
            ->assertRedirect();

        $this->assertSame(ApplicationStatus::Withdrawn, $application->fresh()->status);
    }

    public function test_stranger_cannot_manage_applications(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();
        $stranger = User::factory()->create();
        $opportunity = $this->openOpportunity($venueOwner);
        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Alvo',
            'kind' => ArtistKind::Dj,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $opportunity), [
            'artist_profile_id' => $artist->id,
        ]);

        $application = Application::query()->first();

        $this->actingAs($stranger)->patch(
            route('opportunities.applications.status', [$opportunity, $application]),
            ['status' => ApplicationStatus::Rejected->value]
        )->assertForbidden();
    }

    public function test_applications_index_lists_own_submissions(): void
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();
        $opportunity = $this->openOpportunity($venueOwner);
        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Lista Band',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($artistOwner)->post(route('opportunities.applications.store', $opportunity), [
            'artist_profile_id' => $artist->id,
        ]);

        $this->actingAs($artistOwner)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Sexta ao vivo')
            ->assertSee('Lista Band');
    }
}
