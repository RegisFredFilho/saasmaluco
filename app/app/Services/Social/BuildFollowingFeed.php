<?php

namespace App\Services\Social;

use App\Enums\OpportunityStatus;
use App\Models\ArtistProfile;
use App\Models\Follow;
use App\Models\Opportunity;
use App\Models\Post;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BuildFollowingFeed
{
    /**
     * @return Collection<int, array{type: string, at: Carbon, item: Post|Opportunity}>
     */
    public function handle(User $user, int $limit = 30): Collection
    {
        $follows = Follow::query()
            ->where('user_id', $user->id)
            ->get(['followable_type', 'followable_id']);

        $artistIds = $follows
            ->where('followable_type', ArtistProfile::class)
            ->pluck('followable_id')
            ->all();

        $venueIds = $follows
            ->where('followable_type', VenueProfile::class)
            ->pluck('followable_id')
            ->all();

        $posts = Post::query()
            ->public()
            ->with(['publisher', 'author'])
            ->where(function ($query) use ($artistIds, $venueIds) {
                if ($artistIds !== []) {
                    $query->orWhere(function ($inner) use ($artistIds) {
                        $inner->where('publisher_type', ArtistProfile::class)
                            ->whereIn('publisher_id', $artistIds);
                    });
                }

                if ($venueIds !== []) {
                    $query->orWhere(function ($inner) use ($venueIds) {
                        $inner->where('publisher_type', VenueProfile::class)
                            ->whereIn('publisher_id', $venueIds);
                    });
                }

                if ($artistIds === [] && $venueIds === []) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->latest()
            ->limit($limit)
            ->get();

        $opportunities = Opportunity::query()
            ->where('status', OpportunityStatus::Open)
            ->whereIn('venue_profile_id', $venueIds ?: [0])
            ->with('venueProfile')
            ->latest('created_at')
            ->limit($limit)
            ->get();

        return $posts
            ->map(fn (Post $post) => [
                'type' => 'post',
                'at' => $post->created_at,
                'item' => $post,
            ])
            ->concat(
                $opportunities->map(fn (Opportunity $opportunity) => [
                    'type' => 'opportunity',
                    'at' => $opportunity->created_at ?? $opportunity->event_date,
                    'item' => $opportunity,
                ])
            )
            ->sortByDesc('at')
            ->take($limit)
            ->values();
    }
}
