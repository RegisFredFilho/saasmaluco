<?php

namespace Tests\Feature;

use App\Enums\ArtistKind;
use App\Enums\ArtistMediaType;
use App\Models\ArtistExperience;
use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\GenreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtistPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GenreSeeder::class);
    }

    public function test_public_artist_page_is_visible_to_guests(): void
    {
        $owner = User::factory()->create();
        $profile = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Aberta',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'bio' => 'Tocamos rock.',
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->get(route('artists.show', $profile))
            ->assertOk()
            ->assertSee('Banda Aberta')
            ->assertSee('Tocamos rock.')
            ->assertDontSee($owner->email);
    }

    public function test_private_artist_page_is_forbidden_to_strangers(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $profile = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Projeto Privado',
            'kind' => ArtistKind::Solo,
            'is_public' => false,
        ]);

        $this->get(route('artists.show', $profile))->assertForbidden();

        $this->actingAs($stranger)
            ->get(route('artists.show', $profile))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('artists.show', $profile))
            ->assertOk();
    }

    public function test_owner_can_update_progressive_profile_fields_and_genres(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $profile = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'DJ Teste',
            'kind' => ArtistKind::Dj,
            'is_public' => true,
        ]);
        $genre = Genre::query()->first();

        $response = $this->actingAs($owner)->put(route('artist-profiles.update', $profile), [
            'display_name' => 'DJ Teste',
            'stage_name' => 'DJ Neon',
            'kind' => ArtistKind::Dj->value,
            'is_public' => '1',
            'bio' => 'Sets eletrônicos',
            'city' => 'São Paulo',
            'state' => 'SP',
            'fee_min' => '800.00',
            'fee_max' => '1500.00',
            'genre_ids' => [$genre->id],
            'photo' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
            'links' => [
                ['label' => 'Site', 'url' => 'https://example.com'],
            ],
        ]);

        $response->assertRedirect(route('artist-profiles.edit', $profile));
        $profile->refresh();

        $this->assertSame('DJ Neon', $profile->stage_name);
        $this->assertSame('São Paulo', $profile->city);
        $this->assertTrue($profile->genres->contains($genre));
        $this->assertNotNull($profile->photo_path);
        $this->assertSame(1, $profile->links()->count());
        Storage::disk('public')->assertExists($profile->photo_path);
    }

    public function test_stranger_cannot_update_artist_profile(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $profile = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Artista',
            'kind' => ArtistKind::Solo,
            'is_public' => true,
        ]);

        $this->actingAs($stranger)
            ->put(route('artist-profiles.update', $profile), [
                'display_name' => 'Hackeado',
                'kind' => ArtistKind::Solo->value,
            ])
            ->assertForbidden();

        $this->assertSame('Artista', $profile->fresh()->display_name);
    }

    public function test_manual_experience_is_never_verified_and_verified_flag_cannot_be_forced(): void
    {
        $owner = User::factory()->create();
        $profile = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda',
            'kind' => ArtistKind::Band,
            'is_public' => true,
        ]);

        $this->actingAs($owner)->post(route('artist-profiles.experiences.store', $profile), [
            'title' => 'Show no Bar X',
            'venue_name' => 'Bar X',
            'is_verified' => '1',
            'source' => 'booking',
            'booking_id' => 999,
        ])->assertRedirect();

        $experience = ArtistExperience::query()->first();
        $this->assertFalse($experience->is_verified);
        $this->assertSame('manual', $experience->source);
        $this->assertNull($experience->booking_id);
    }

    public function test_video_link_must_be_youtube_or_vimeo(): void
    {
        $owner = User::factory()->create();
        $profile = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda',
            'kind' => ArtistKind::Band,
            'is_public' => true,
        ]);

        $this->actingAs($owner)->post(route('artist-profiles.media.store', $profile), [
            'type' => ArtistMediaType::VideoLink->value,
            'external_url' => 'https://example.com/video',
        ])->assertSessionHasErrors('external_url');

        $this->actingAs($owner)->post(route('artist-profiles.media.store', $profile), [
            'type' => ArtistMediaType::VideoLink->value,
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'caption' => 'Clipe',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $profile->media()->count());
    }
}
