<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Enums\VenueEventType;
use App\Enums\VenueManagerRole;
use App\Models\Genre;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\VenueManager;
use App\Models\VenueProfile;
use Database\Seeders\GenreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VenuePublicProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GenreSeeder::class);
    }

    public function test_public_venue_page_is_visible_to_guests(): void
    {
        $owner = User::factory()->create();
        $profile = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Bar Aberto',
            'is_public' => true,
            'description' => 'Casa de shows no centro.',
            'city' => 'Curitiba',
            'state' => 'PR',
            'contact_email' => 'contato@baraberto.test',
        ]);

        $this->get(route('venues.show', $profile))
            ->assertOk()
            ->assertSee('Bar Aberto')
            ->assertSee('Casa de shows no centro.')
            ->assertSee('contato@baraberto.test')
            ->assertDontSee($owner->email);
    }

    public function test_private_venue_page_is_forbidden_to_strangers(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $profile = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Espaço Privado',
            'is_public' => false,
        ]);

        $this->get(route('venues.show', $profile))->assertForbidden();

        $this->actingAs($stranger)
            ->get(route('venues.show', $profile))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('venues.show', $profile))
            ->assertOk();
    }

    public function test_owner_can_update_venue_fields_genres_and_managers(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $manager = User::factory()->create(['email' => 'gestor@venue.test']);
        $profile = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Casa Live',
            'is_public' => true,
        ]);
        $genre = Genre::query()->first();

        $response = $this->actingAs($owner)->put(route('venue-profiles.update', $profile), [
            'display_name' => 'Casa Live Atualizada',
            'description' => 'Palco e som.',
            'city' => 'São Paulo',
            'state' => 'SP',
            'capacity' => 200,
            'event_type' => VenueEventType::LiveMusic->value,
            'budget_min' => 500,
            'budget_max' => 2000,
            'contact_email' => 'bookings@casalive.test',
            'is_public' => '1',
            'genre_ids' => [$genre->id],
            'manager_emails' => ['gestor@venue.test'],
            'cover' => UploadedFile::fake()->image('capa.jpg'),
        ]);

        $response->assertRedirect(route('venue-profiles.edit', $profile));

        $profile->refresh();
        $this->assertSame('Casa Live Atualizada', $profile->display_name);
        $this->assertSame('São Paulo', $profile->city);
        $this->assertTrue($profile->genres->contains($genre));
        $this->assertNotNull($profile->cover_path);
        $this->assertTrue(
            $profile->managers()->where('user_id', $manager->id)->where('role', VenueManagerRole::Admin->value)->exists()
        );
        $this->assertTrue(
            $profile->managers()->where('user_id', $owner->id)->where('role', VenueManagerRole::Owner->value)->exists()
        );
    }

    public function test_manager_can_edit_venue_and_create_opportunity(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $profile = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Pub Norte',
            'is_public' => true,
        ]);
        VenueManager::query()->create([
            'venue_profile_id' => $profile->id,
            'user_id' => $manager->id,
            'role' => VenueManagerRole::Admin,
        ]);

        $this->actingAs($manager)
            ->get(route('venue-profiles.edit', $profile))
            ->assertOk();

        $this->actingAs($manager)->post(route('venue-profiles.opportunities.store', $profile), [
            'title' => 'Sexta acústica',
            'description' => 'Uma noite de voz e violão.',
            'event_date' => now()->addWeek()->toDateString(),
            'budget_min' => 800,
            'slots_count' => 1,
            'status' => OpportunityStatus::Open->value,
        ])->assertRedirect(route('venue-profiles.edit', $profile));

        $opportunity = Opportunity::query()->first();
        $this->assertNotNull($opportunity);
        $this->assertSame(OpportunityStatus::Open, $opportunity->status);
        $this->assertSame($manager->id, $opportunity->created_by_user_id);

        $this->get(route('opportunities.show', $opportunity))
            ->assertOk()
            ->assertSee('Sexta acústica');
    }

    public function test_draft_opportunity_is_hidden_from_guests(): void
    {
        $owner = User::factory()->create();
        $profile = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Club Draft',
            'is_public' => true,
        ]);
        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $profile->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Rascunho secreto',
            'status' => OpportunityStatus::Draft,
        ]);

        $this->get(route('opportunities.show', $opportunity))->assertForbidden();

        $this->actingAs($owner)
            ->get(route('opportunities.show', $opportunity))
            ->assertOk();
    }

    public function test_owner_can_transition_opportunity_status(): void
    {
        $owner = User::factory()->create();
        $profile = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Arena Status',
            'is_public' => true,
        ]);
        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $profile->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Noite jazz',
            'status' => OpportunityStatus::Draft,
        ]);

        $this->actingAs($owner)->patch(
            route('venue-profiles.opportunities.status', [$profile, $opportunity]),
            ['status' => OpportunityStatus::Open->value]
        )->assertRedirect();

        $this->assertSame(OpportunityStatus::Open, $opportunity->fresh()->status);

        $this->actingAs($owner)->patch(
            route('venue-profiles.opportunities.status', [$profile, $opportunity]),
            ['status' => OpportunityStatus::Draft->value]
        )->assertSessionHasErrors('status');
    }

    public function test_owner_can_add_and_remove_gallery_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $profile = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Galeria Live',
            'is_public' => true,
        ]);

        $this->actingAs($owner)->post(route('venue-profiles.media.store', $profile), [
            'photo' => UploadedFile::fake()->image('sala.jpg'),
            'caption' => 'Sala principal',
        ])->assertRedirect();

        $media = $profile->media()->first();
        $this->assertNotNull($media);

        $this->actingAs($owner)
            ->delete(route('venue-profiles.media.destroy', [$profile, $media]))
            ->assertRedirect();

        $this->assertSame(0, $profile->media()->count());
    }
}
