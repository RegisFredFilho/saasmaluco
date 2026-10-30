<?php

namespace Tests\Feature;

use App\Enums\ArtistKind;
use App\Enums\OpportunityStatus;
use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\VenueProfile;
use Database\Seeders\GenreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GenreSeeder::class);
    }

    public function test_discoverable_artists_appear_in_list_and_incomplete_are_hidden(): void
    {
        $owner = User::factory()->create();

        $visible = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Visível',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Sem Cidade',
            'kind' => ArtistKind::Solo,
            'is_public' => true,
        ]);

        ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Privado PR',
            'kind' => ArtistKind::Solo,
            'is_public' => false,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->get(route('artists.index'))
            ->assertOk()
            ->assertSee('Banda Visível')
            ->assertDontSee('Sem Cidade')
            ->assertDontSee('Privado PR');

        $this->get(route('artists.index', ['city' => 'Curitiba', 'state' => 'PR']))
            ->assertOk()
            ->assertSee('Banda Visível');

        $this->get(route('artists.index', ['q' => 'InexistenteXYZ']))
            ->assertOk()
            ->assertDontSee('Banda Visível');

        $this->assertSame($visible->id, ArtistProfile::query()->discoverable()->first()->id);
    }

    public function test_artist_genre_filter_works(): void
    {
        $owner = User::factory()->create();
        $rock = Genre::query()->where('slug', 'rock')->first()
            ?? Genre::query()->first();
        $other = Genre::query()->where('id', '!=', $rock->id)->first();

        $withGenre = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Rock Band',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);
        $withGenre->genres()->sync([$rock->id]);

        $without = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Outro Estilo',
            'kind' => ArtistKind::Solo,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);
        $without->genres()->sync([$other->id]);

        $this->get(route('artists.index', ['genre_id' => $rock->id]))
            ->assertOk()
            ->assertSee('Rock Band')
            ->assertDontSee('Outro Estilo');
    }

    public function test_venue_discovery_hides_contact_and_incomplete_profiles(): void
    {
        $owner = User::factory()->create(['email' => 'dono-secreto@example.test']);
        VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Bar Centro',
            'is_public' => true,
            'city' => 'São Paulo',
            'state' => 'SP',
            'contact_email' => 'bookings@barcentro.test',
            'contact_phone' => '11999999999',
        ]);
        VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Sem Local',
            'is_public' => true,
        ]);

        $this->get(route('venues.index', ['city' => 'São Paulo']))
            ->assertOk()
            ->assertSee('Bar Centro')
            ->assertDontSee('Sem Local')
            ->assertDontSee('bookings@barcentro.test')
            ->assertDontSee('11999999999')
            ->assertDontSee('dono-secreto@example.test');
    }

    public function test_opportunity_discovery_lists_only_open_on_discoverable_venues(): void
    {
        $owner = User::factory()->create();
        $venue = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Casa Aberta',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);
        $privateVenue = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Casa Privada',
            'is_public' => false,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Sexta ao vivo',
            'status' => OpportunityStatus::Open,
            'event_date' => now()->addDays(10)->toDateString(),
        ]);
        Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Rascunho interno',
            'status' => OpportunityStatus::Draft,
        ]);
        Opportunity::query()->create([
            'venue_profile_id' => $privateVenue->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Gig privada',
            'status' => OpportunityStatus::Open,
        ]);

        $this->get(route('opportunities.index'))
            ->assertOk()
            ->assertSee('Sexta ao vivo')
            ->assertDontSee('Rascunho interno')
            ->assertDontSee('Gig privada');

        $this->get(route('opportunities.index', ['state' => 'PR', 'city' => 'Curitiba']))
            ->assertOk()
            ->assertSee('Sexta ao vivo');
    }

    public function test_discovery_paginates_artists(): void
    {
        $owner = User::factory()->create();

        for ($i = 1; $i <= 13; $i++) {
            ArtistProfile::create([
                'owner_user_id' => $owner->id,
                'display_name' => sprintf('Artista Pag %02d', $i),
                'kind' => ArtistKind::Solo,
                'is_public' => true,
                'city' => 'Curitiba',
                'state' => 'PR',
            ]);
        }

        $this->get(route('artists.index'))
            ->assertOk()
            ->assertSee('Artista Pag 01')
            ->assertDontSee('Artista Pag 13');

        $this->get(route('artists.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Artista Pag 13');
    }
}
