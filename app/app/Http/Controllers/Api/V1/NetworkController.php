<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ArtistProfile;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\VenueProfile;
use App\Support\NetworkTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NetworkController extends Controller
{
    public function followArtist(Request $request, ArtistProfile $artist): JsonResponse
    {
        return $this->follow($request, $artist);
    }

    public function unfollowArtist(Request $request, ArtistProfile $artist): JsonResponse
    {
        return $this->unfollow($request, $artist);
    }

    public function favoriteArtist(Request $request, ArtistProfile $artist): JsonResponse
    {
        return $this->favorite($request, $artist);
    }

    public function unfavoriteArtist(Request $request, ArtistProfile $artist): JsonResponse
    {
        return $this->unfavorite($request, $artist);
    }

    public function followVenue(Request $request, VenueProfile $venue): JsonResponse
    {
        return $this->follow($request, $venue);
    }

    public function unfollowVenue(Request $request, VenueProfile $venue): JsonResponse
    {
        return $this->unfollow($request, $venue);
    }

    public function favoriteVenue(Request $request, VenueProfile $venue): JsonResponse
    {
        return $this->favorite($request, $venue);
    }

    public function unfavoriteVenue(Request $request, VenueProfile $venue): JsonResponse
    {
        return $this->unfavorite($request, $venue);
    }

    private function follow(Request $request, Model $target): JsonResponse
    {
        $this->authorize('view', $target);
        $user = $request->user();

        if (NetworkTarget::isOwn($user, $target)) {
            throw ValidationException::withMessages([
                'follow' => ['Você não pode seguir o próprio perfil.'],
            ]);
        }

        if (! $target->is_public) {
            throw ValidationException::withMessages([
                'follow' => ['Só é possível seguir perfis públicos.'],
            ]);
        }

        Follow::query()->firstOrCreate([
            'user_id' => $user->id,
            'followable_type' => $target->getMorphClass(),
            'followable_id' => $target->getKey(),
        ]);

        return response()->json(['following' => true]);
    }

    private function unfollow(Request $request, Model $target): JsonResponse
    {
        $user = $request->user();

        Follow::query()
            ->where('user_id', $user->id)
            ->where('followable_type', $target->getMorphClass())
            ->where('followable_id', $target->getKey())
            ->delete();

        return response()->json(['following' => false]);
    }

    private function favorite(Request $request, Model $target): JsonResponse
    {
        $this->authorize('view', $target);
        $user = $request->user();

        if (NetworkTarget::isOwn($user, $target)) {
            throw ValidationException::withMessages([
                'favorite' => ['Você não pode favoritar o próprio perfil.'],
            ]);
        }

        if (! $target->is_public) {
            throw ValidationException::withMessages([
                'favorite' => ['Só é possível favoritar perfis públicos.'],
            ]);
        }

        Favorite::query()->firstOrCreate([
            'user_id' => $user->id,
            'favoritable_type' => $target->getMorphClass(),
            'favoritable_id' => $target->getKey(),
        ]);

        return response()->json(['favorited' => true]);
    }

    private function unfavorite(Request $request, Model $target): JsonResponse
    {
        $user = $request->user();

        Favorite::query()
            ->where('user_id', $user->id)
            ->where('favoritable_type', $target->getMorphClass())
            ->where('favoritable_id', $target->getKey())
            ->delete();

        return response()->json(['favorited' => false]);
    }
}
