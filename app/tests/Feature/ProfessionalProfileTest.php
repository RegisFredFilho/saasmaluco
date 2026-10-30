<?php

namespace Tests\Feature;

use App\Enums\ArtistKind;
use App\Enums\ArtistMemberRole;
use App\Models\ArtistProfile;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_artist_profile_and_becomes_owner_member(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('artist-profiles.store'), [
            'display_name' => 'Banda Teste',
            'kind' => ArtistKind::Band->value,
            'is_public' => '1',
        ]);

        $profile = ArtistProfile::query()->first();

        $this->assertNotNull($profile);
        $response->assertRedirect(route('artist-profiles.edit', $profile));
        $this->assertSame($user->id, $profile->owner_user_id);
        $this->assertTrue(
            $profile->members()->where('user_id', $user->id)->where('role', ArtistMemberRole::Owner->value)->exists()
        );
    }

    public function test_user_cannot_edit_another_users_artist_profile(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $profile = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Artista Dono',
            'kind' => ArtistKind::Solo,
            'is_public' => true,
        ]);

        $this->actingAs($intruder)
            ->get(route('artist-profiles.edit', $profile))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->put(route('artist-profiles.update', $profile), [
                'display_name' => 'Hack',
                'kind' => ArtistKind::Solo->value,
            ])
            ->assertForbidden();
    }

    public function test_user_can_create_and_update_own_venue_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('venue-profiles.store'), [
            'display_name' => 'Bar do Centro',
            'is_public' => '1',
        ])->assertRedirect();

        $venue = VenueProfile::query()->first();
        $this->assertSame($user->id, $venue->owner_user_id);

        $this->actingAs($user)->put(route('venue-profiles.update', $venue), [
            'display_name' => 'Bar Atualizado',
            'is_public' => '1',
        ])->assertRedirect(route('venue-profiles.edit', $venue));

        $this->assertSame('Bar Atualizado', $venue->fresh()->display_name);
    }

    public function test_user_cannot_edit_another_users_venue_profile(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Casa Show',
            'is_public' => true,
        ]);

        $this->actingAs($intruder)
            ->put(route('venue-profiles.update', $venue), [
                'display_name' => 'Invasão',
            ])
            ->assertForbidden();
    }
}
