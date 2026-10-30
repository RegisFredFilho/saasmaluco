<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ArtistKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArtistProfileRequest;
use App\Http\Resources\ArtistResource;
use App\Models\ArtistLink;
use App\Models\ArtistProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MyArtistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ArtistProfile::class);

        $userId = $request->user()->id;

        $profiles = ArtistProfile::query()
            ->where(function ($query) use ($userId) {
                $query->where('owner_user_id', $userId)
                    ->orWhereHas('members', fn ($q) => $q->where('user_id', $userId));
            })
            ->with('genres')
            ->latest()
            ->paginate(20);

        return ArtistResource::collection($profiles);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ArtistProfile::class);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::enum(ArtistKind::class)],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $profile = ArtistProfile::create([
            'owner_user_id' => $request->user()->id,
            'display_name' => $data['display_name'],
            'kind' => $data['kind'],
            'is_public' => $request->boolean('is_public', true),
        ]);

        return ArtistResource::make($profile->load('genres'))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateArtistProfileRequest $request, ArtistProfile $artistProfile): ArtistResource
    {
        $data = $request->safe()->except(['photo', 'cover', 'genre_ids', 'links']);

        if ($request->hasFile('photo')) {
            if ($artistProfile->photo_path) {
                Storage::disk('public')->delete($artistProfile->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('artists/photos', 'public');
        }

        if ($request->hasFile('cover')) {
            if ($artistProfile->cover_path) {
                Storage::disk('public')->delete($artistProfile->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('artists/covers', 'public');
        }

        if ($request->exists('is_public')) {
            $data['is_public'] = $request->boolean('is_public');
        }

        $artistProfile->update($data);

        if ($request->exists('genre_ids')) {
            $artistProfile->genres()->sync($request->input('genre_ids', []));
        }

        if ($request->exists('links')) {
            $this->syncLinks($artistProfile, $request->input('links', []));
        }

        return ArtistResource::make($artistProfile->fresh()->load('genres'));
    }

    /**
     * @param  array<int, array{label?: string, url?: string}>  $links
     */
    private function syncLinks(ArtistProfile $artistProfile, array $links): void
    {
        $artistProfile->links()->delete();

        $order = 0;
        foreach ($links as $link) {
            if (blank($link['url'] ?? null) || blank($link['label'] ?? null)) {
                continue;
            }

            ArtistLink::query()->create([
                'artist_profile_id' => $artistProfile->id,
                'label' => $link['label'],
                'url' => $link['url'],
                'sort_order' => $order++,
            ]);
        }
    }
}
