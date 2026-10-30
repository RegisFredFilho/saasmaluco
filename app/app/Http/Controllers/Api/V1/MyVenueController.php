<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVenueProfileRequest;
use App\Http\Resources\VenueResource;
use App\Models\User;
use App\Models\VenueManager;
use App\Models\VenueProfile;
use App\Enums\VenueManagerRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class MyVenueController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', VenueProfile::class);

        $userId = $request->user()->id;

        $profiles = VenueProfile::query()
            ->where(function ($query) use ($userId) {
                $query->where('owner_user_id', $userId)
                    ->orWhereHas('managers', fn ($q) => $q->where('user_id', $userId));
            })
            ->with('genres')
            ->latest()
            ->paginate(20);

        return VenueResource::collection($profiles);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', VenueProfile::class);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $profile = VenueProfile::create([
            'owner_user_id' => $request->user()->id,
            'display_name' => $data['display_name'],
            'is_public' => $request->boolean('is_public', true),
        ]);

        return VenueResource::make($profile->load('genres'))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateVenueProfileRequest $request, VenueProfile $venueProfile): VenueResource
    {
        $data = $request->safe()->except(['cover', 'genre_ids', 'manager_emails']);

        if ($request->hasFile('cover')) {
            if ($venueProfile->cover_path) {
                Storage::disk('public')->delete($venueProfile->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('venues/covers', 'public');
        }

        if ($request->exists('is_public')) {
            $data['is_public'] = $request->boolean('is_public');
        }

        $venueProfile->update($data);

        if ($request->exists('genre_ids')) {
            $venueProfile->genres()->sync($request->input('genre_ids', []));
        }

        if ($request->exists('manager_emails')) {
            $this->syncManagers($venueProfile, $request->input('manager_emails', []));
        }

        return VenueResource::make($venueProfile->fresh()->load('genres'));
    }

    /**
     * @param  array<int, string|null>  $emails
     */
    private function syncManagers(VenueProfile $venueProfile, array $emails): void
    {
        $ownerId = $venueProfile->owner_user_id;
        $emails = collect($emails)
            ->filter(fn ($email) => filled($email))
            ->map(fn ($email) => strtolower(trim($email)))
            ->unique()
            ->values();

        $keepUserIds = [$ownerId];

        foreach ($emails as $email) {
            $user = User::query()->where('email', $email)->first();
            if (! $user || $user->id === $ownerId || ! $user->isActive()) {
                continue;
            }

            VenueManager::query()->updateOrCreate(
                [
                    'venue_profile_id' => $venueProfile->id,
                    'user_id' => $user->id,
                ],
                ['role' => VenueManagerRole::Admin],
            );

            $keepUserIds[] = $user->id;
        }

        VenueManager::query()
            ->where('venue_profile_id', $venueProfile->id)
            ->whereNotIn('user_id', $keepUserIds)
            ->delete();

        VenueManager::query()->firstOrCreate(
            [
                'venue_profile_id' => $venueProfile->id,
                'user_id' => $ownerId,
            ],
            ['role' => VenueManagerRole::Owner],
        );
    }
}
