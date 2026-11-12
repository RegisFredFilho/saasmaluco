<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ArtistKind;
use App\Enums\OpportunityStatus;
use App\Models\AppNotification;
use App\Models\ArtistProfile;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson(['ok' => true, 'version' => 'v1']);
    }

    public function test_login_issues_token_and_me_requires_auth(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => 'password',
        ]);

        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'api@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email']]);

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'api@example.com');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Revogação real do token (Auth de guard pode persistir entre calls no PHPUnit).
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertNotNull($user->fresh());
    }

    public function test_deactivated_user_cannot_login_via_api(): void
    {
        User::factory()->deactivated()->create([
            'email' => 'off@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'off@example.com',
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_public_discovery_lists_artists_and_opportunities(): void
    {
        $owner = User::factory()->create();

        ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda API',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Privado',
            'kind' => ArtistKind::Solo,
            'is_public' => false,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $venue = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Casa API',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Gig API',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->addDays(10)->toDateString(),
            'starts_at' => '21:00:00',
            'ends_at' => '23:00:00',
        ]);

        $this->getJson('/api/v1/artists')
            ->assertOk()
            ->assertJsonFragment(['display_name' => 'Banda API'])
            ->assertJsonMissing(['display_name' => 'Privado']);

        $this->getJson('/api/v1/opportunities')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Gig API']);

        $this->getJson('/api/v1/venues')
            ->assertOk()
            ->assertJsonFragment(['display_name' => 'Casa API']);
    }

    public function test_notifications_require_token_and_are_scoped(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        AppNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Minha notif',
            'body' => 'Olá',
            'idempotency_key' => 'api-notif-user-'.$user->id,
        ]);

        AppNotification::query()->create([
            'user_id' => $other->id,
            'type' => 'test',
            'title' => 'Alheia',
            'body' => 'Segredo',
            'idempotency_key' => 'api-notif-user-'.$other->id,
        ]);

        $this->getJson('/api/v1/notifications')->assertUnauthorized();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Minha notif'])
            ->assertJsonMissing(['title' => 'Alheia']);
    }
}
