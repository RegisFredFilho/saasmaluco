<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\VenueProfile;
use App\Services\Social\BuildFollowingFeed;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NetworkController extends Controller
{
    public function index(Request $request, BuildFollowingFeed $feedBuilder): View
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
            'feedItems' => $feedBuilder->handle($user),
        ]);
    }
}
