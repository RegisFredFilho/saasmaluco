<?php

namespace App\Http\Controllers;

use App\Enums\OpportunityStatus;
use App\Models\ArtistProfile;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\Opportunity;
use App\Models\VenueProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NetworkController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $follows = Follow::query()
            ->where('user_id', $user->id)
            ->with('followable')
            ->latest()
            ->get();

        $favorites = Favorite::query()
            ->where('user_id', $user->id)
            ->with('favoritable')
            ->latest()
            ->get();

        $followedVenueIds = $follows
            ->filter(fn (Follow $f) => $f->followable instanceof VenueProfile)
            ->pluck('followable_id')
            ->all();

        $feed = Opportunity::query()
            ->where('status', OpportunityStatus::Open)
            ->whereIn('venue_profile_id', $followedVenueIds ?: [0])
            ->with('venueProfile')
            ->orderBy('event_date')
            ->limit(15)
            ->get();

        return view('network.index', [
            'followedArtists' => $follows
                ->filter(fn (Follow $f) => $f->followable instanceof ArtistProfile)
                ->values(),
            'followedVenues' => $follows
                ->filter(fn (Follow $f) => $f->followable instanceof VenueProfile)
                ->values(),
            'favoriteArtists' => $favorites
                ->filter(fn (Favorite $f) => $f->favoritable instanceof ArtistProfile)
                ->values(),
            'favoriteVenues' => $favorites
                ->filter(fn (Favorite $f) => $f->favoritable instanceof VenueProfile)
                ->values(),
            'feed' => $feed,
        ]);
    }
}
