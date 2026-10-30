<?php

namespace Tests\Feature;

use App\Enums\ArtistKind;
use App\Enums\OpportunityStatus;
use App\Models\ArtistProfile;
use App\Models\Follow;
use App\Models\Opportunity;
use App\Models\Post;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_publish_as_artist_and_stranger_cannot(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $artist = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Post',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($owner)
            ->post(route('posts.store'), [
                'publisher_key' => 'artist:'.$artist->id,
                'body' => 'Show novo sexta-feira!',
                'is_public' => '1',
            ])
            ->assertRedirect(route('artists.show', $artist));

        $this->assertDatabaseHas('posts', [
            'author_user_id' => $owner->id,
            'publisher_type' => ArtistProfile::class,
            'publisher_id' => $artist->id,
            'body' => 'Show novo sexta-feira!',
        ]);

        $this->get(route('artists.show', $artist))
            ->assertOk()
            ->assertSee('Show novo sexta-feira!');

        $this->actingAs($stranger)
            ->post(route('posts.store'), [
                'publisher_key' => 'artist:'.$artist->id,
                'body' => 'Hack',
                'is_public' => '1',
            ])
            ->assertForbidden();
    }

    public function test_user_can_publish_as_self_and_delete_own_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('posts.store'), [
                'publisher_key' => 'user',
                'body' => 'Post pessoal',
                'is_public' => '1',
            ])
            ->assertRedirect(route('network.index'));

        $post = Post::query()->first();
        $this->assertNotNull($post);
        $this->assertSame(User::class, $post->publisher_type);

        $this->actingAs($user)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect();

        $this->assertSoftDeleted($post);
    }

    public function test_following_feed_includes_posts_and_opportunities(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();

        $artist = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'DJ Feed',
            'kind' => ArtistKind::Dj,
            'is_public' => true,
        ]);

        $venue = VenueProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Casa Feed',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        Follow::query()->create([
            'user_id' => $viewer->id,
            'followable_type' => ArtistProfile::class,
            'followable_id' => $artist->id,
        ]);
        Follow::query()->create([
            'user_id' => $viewer->id,
            'followable_type' => VenueProfile::class,
            'followable_id' => $venue->id,
        ]);

        Post::query()->create([
            'author_user_id' => $owner->id,
            'publisher_type' => ArtistProfile::class,
            'publisher_id' => $artist->id,
            'body' => 'Post no feed',
            'is_public' => true,
        ]);

        Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $owner->id,
            'title' => 'Gig no feed',
            'status' => OpportunityStatus::Open,
            'event_date' => now()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($viewer)
            ->get(route('network.index'))
            ->assertOk()
            ->assertSee('Post no feed')
            ->assertSee('Gig no feed')
            ->assertSee('Oportunidade')
            ->assertSee('Publicação');
    }
}
