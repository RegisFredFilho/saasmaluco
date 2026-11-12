<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Models\Favorite;
use App\Models\VenueProfile;
use App\Support\NetworkTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function storeArtist(Request $request, ArtistProfile $artistProfile): RedirectResponse
    {
        return $this->store($request, $artistProfile);
    }

    public function destroyArtist(Request $request, ArtistProfile $artistProfile): RedirectResponse
    {
        return $this->destroy($request, $artistProfile);
    }

    public function storeVenue(Request $request, VenueProfile $venueProfile): RedirectResponse
    {
        return $this->store($request, $venueProfile);
    }

    public function destroyVenue(Request $request, VenueProfile $venueProfile): RedirectResponse
    {
        return $this->destroy($request, $venueProfile);
    }

    private function store(Request $request, Model $target): RedirectResponse
    {
        $this->authorize('view', $target);
        $user = $request->user();

        abort_unless($user->isActive(), 403);

        if (NetworkTarget::isOwn($user, $target)) {
            return back()->withErrors(['favorite' => 'Você não pode favoritar o próprio perfil.']);
        }

        if (! $target->is_public) {
            return back()->withErrors(['favorite' => 'Só é possível favoritar perfis públicos.']);
        }

        Favorite::query()->firstOrCreate([
            'user_id' => $user->id,
            'favoritable_type' => $target->getMorphClass(),
            'favoritable_id' => $target->getKey(),
        ]);

        return back()->with('status', 'favorited');
    }

    private function destroy(Request $request, Model $target): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isActive(), 403);

        Favorite::query()
            ->where('user_id', $user->id)
            ->where('favoritable_type', $target->getMorphClass())
            ->where('favoritable_id', $target->getKey())
            ->delete();

        return back()->with('status', 'unfavorited');
    }
}
