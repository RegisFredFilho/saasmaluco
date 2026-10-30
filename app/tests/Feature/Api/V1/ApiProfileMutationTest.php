<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ArtistKind;
use App\Models\ArtistProfile;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiProfileMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_account_via_api(): void
    {
        $user = User::factory()->create([
            'name' => 'Antes',
            'preferences' => ['notify_email' => true],
        ]);

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/auth/me', [
            'name' => 'Depois API',
            'email' => $user->email,
            'city' => 'Curitiba',
            'state' => 'PR',
            'notify_email' => false,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Depois API')
            ->assertJsonPath('data.city', 'Curitiba');

        $user->refresh();
        $this->assertSame('Depois API', $user->name);
        $this->assertFalse((bool) data_get($user->preferences, 'notify_email'));
    }

    public function test_user_can_create_and_update_artist_profile_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/me/artists', [
            'display_name' => 'Banda API Me',
            'kind' => ArtistKind::Band->value,
            'is_public' => true,
        ])->assertCreated()
            ->assertJsonPath('data.display_name', 'Banda API Me');

        $id = $create->json('data.id');

        $this->patchJson("/api/v1/me/artists/{$id}", [
            'display_name' => 'Banda API Atualizada',
            'kind' => ArtistKind::Band->value,
            'bio' => 'Bio via API',
            'city' => 'Curitiba',
            'state' => 'PR',
            'is_public' => true,
        ])->assertOk()
            ->assertJsonPath('data.display_name', 'Banda API Atualizada')
            ->assertJsonPath('data.bio', 'Bio via API');

        $this->getJson('/api/v1/me/artists')
            ->assertOk()
            ->assertJsonFragment(['id' => $id]);

        $stranger = User::factory()->create();
        Sanctum::actingAs($stranger);
        $this->patchJson("/api/v1/me/artists/{$id}", [
            'display_name' => 'Hack',
            'kind' => ArtistKind::Band->value,
        ])->assertForbidden();
    }

    public function test_user_can_create_and_update_venue_profile_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/me/venues', [
            'display_name' => 'Casa API Me',
            'is_public' => true,
        ])->assertCreated();

        $id = $create->json('data.id');

        $this->patchJson("/api/v1/me/venues/{$id}", [
            'display_name' => 'Casa API Atualizada',
            'description' => 'Espaço via API',
            'city' => 'Curitiba',
            'state' => 'PR',
            'is_public' => true,
        ])->assertOk()
            ->assertJsonPath('data.display_name', 'Casa API Atualizada')
            ->assertJsonPath('data.description', 'Espaço via API');

        $this->assertDatabaseHas('venue_profiles', [
            'id' => $id,
            'display_name' => 'Casa API Atualizada',
        ]);
    }
}
