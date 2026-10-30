<?php

namespace Tests\Feature;

use App\Enums\ArtistKind;
use App\Enums\OpportunityStatus;
use App\Models\ArtistProfile;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_and_unfollow_public_artist(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $artist = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Seguir',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($fan)
            ->post(route('artists.follow', $artist))
            ->assertRedirect();

        $this->assertDatabaseHas('follows', [
            'user_id' => $fan->id,
            'followable_type' => ArtistProfile::class,
            'followable_id' => $artist->id,
        ]);

        $this->actingAs($fan)
            ->post(route('artists.follow', $artist))
            ->assertRedirect();

        $this->assertSame(1, Follow::query()->where('user_id', $fan->id)->count());

        $this->get(route('artists.show', $artist))
            ->assertOk()
            ->assertSee('1 seguidor');

        $this->actingAs($fan)
            ->delete(route('artists.unfollow', $artist))
            ->assertRedirect();

        $this->assertSame(0, Follow::query()->where('user_id', $fan->id)->count());
    }

    public function test_user_cannot_follow_own_or_private_profile(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $own = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Meu Perfil',
            'kind' => ArtistKind::Solo,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $private = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Privado',
            'kind' => ArtistKind::Solo,
            'is_public' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('artists.follow', $own))
            ->assertSessionHasErrors('follow');

        $this->actingAs($stranger)
            ->post(route('artists.follow', $private))
            ->assertForbidden();
    }

    public function test_user_can_favorite_venue_and_see_network_feed(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $venue = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Bar Rede',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Noite dos seguidores',
            'status' => OpportunityStatus::Open,
            'event_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->actingAs($fan)
            ->post(route('venues.favorite', $venue))
            ->assertRedirect();

        $this->assertSame(1, Favorite::query()->where('user_id', $fan->id)->count());

        $this->actingAs($fan)
            ->post(route('venues.follow', $venue))
            ->assertRedirect();

        $this->actingAs($fan)
            ->get(route('network.index'))
            ->assertOk()
            ->assertSee('Bar Rede')
            ->assertSee('Noite dos seguidores');
    }

    public function test_guest_cannot_follow(): void
    {
        $owner = User::factory()->create();
        $artist = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Guest Block',
            'kind' => ArtistKind::Dj,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->post(route('artists.follow', $artist))
            ->assertRedirect(route('login'));
    }
}
