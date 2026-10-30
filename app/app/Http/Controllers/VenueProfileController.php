<?php

namespace App\Http\Controllers;

use App\Enums\OpportunityStatus;
use App\Enums\VenueEventType;
use App\Enums\VenueManagerRole;
use App\Http\Requests\UpdateVenueProfileRequest;
use App\Models\Genre;
use App\Models\User;
use App\Models\VenueManager;
use App\Models\VenueMedia;
use App\Models\VenueProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VenueProfileController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', VenueProfile::class);

        $userId = $request->user()->id;

        $profiles = VenueProfile::query()
            ->where(function ($query) use ($userId) {
                $query->where('owner_user_id', $userId)
                    ->orWhereHas('managers', fn ($q) => $q->where('user_id', $userId));
            })
            ->withCount('opportunities')
            ->latest()
            ->get();

        return view('profiles.venues.index', compact('profiles'));
    }

    public function create(): View
    {
        $this->authorize('create', VenueProfile::class);

        return view('profiles.venues.create');
    }

    public function store(Request $request): RedirectResponse
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

        return redirect()
            ->route('venue-profiles.edit', $profile)
            ->with('status', 'venue-created');
    }

    public function show(VenueProfile $venueProfile): View
    {
        $this->authorize('view', $venueProfile);

        $venueProfile->load([
            'genres',
            'media',
            'opportunities' => fn ($q) => $q
                ->whereIn('status', [
                    OpportunityStatus::Open->value,
                    OpportunityStatus::Filled->value,
                    OpportunityStatus::Closed->value,
                ])
                ->latest('event_date')
                ->limit(10),
            'reviews' => fn ($q) => $q->visible()->latest()->limit(20),
            'posts' => fn ($q) => $q->public()->latest()->limit(20),
        ])->loadCount(['follows', 'favorites']);

        $user = auth()->user();

        return view('profiles.venues.show', [
            'profile' => $venueProfile,
            'canManage' => $user ? $user->can('update', $venueProfile) : false,
            'isFollowing' => $user
                ? $venueProfile->follows()->where('user_id', $user->id)->exists()
                : false,
            'isFavorited' => $user
                ? $venueProfile->favorites()->where('user_id', $user->id)->exists()
                : false,
            'isOwn' => $user ? (int) $venueProfile->owner_user_id === (int) $user->id : false,
            'averageRating' => $venueProfile->averageRating(),
            'reviewsCount' => $venueProfile->reviews()->visible()->count(),
        ]);
    }

    public function edit(VenueProfile $venueProfile): View
    {
        $this->authorize('update', $venueProfile);

        $venueProfile->load(['genres', 'media', 'managers.user', 'opportunities' => fn ($q) => $q->latest()]);

        return view('profiles.venues.edit', [
            'profile' => $venueProfile,
            'eventTypes' => VenueEventType::cases(),
            'genres' => Genre::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateVenueProfileRequest $request, VenueProfile $venueProfile): RedirectResponse
    {
        $data = $request->safe()->except(['cover', 'genre_ids', 'manager_emails']);

        if ($request->hasFile('cover')) {
            if ($venueProfile->cover_path) {
                Storage::disk('public')->delete($venueProfile->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('venues/covers', 'public');
        }

        $data['is_public'] = $request->boolean('is_public');
        $venueProfile->update($data);
        $venueProfile->genres()->sync($request->input('genre_ids', []));

        $this->syncManagers($venueProfile, $request->input('manager_emails', []));

        return redirect()
            ->route('venue-profiles.edit', $venueProfile)
            ->with('status', 'venue-updated');
    }

    public function storeMedia(Request $request, VenueProfile $venueProfile): RedirectResponse
    {
        $this->authorize('update', $venueProfile);

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $venueProfile->media()->create([
            'path' => $request->file('photo')->store('venues/gallery', 'public'),
            'caption' => $request->input('caption'),
            'sort_order' => ((int) $venueProfile->media()->max('sort_order')) + 1,
        ]);

        return back()->with('status', 'media-added');
    }

    public function destroyMedia(VenueProfile $venueProfile, VenueMedia $media): RedirectResponse
    {
        $this->authorize('update', $venueProfile);
        abort_unless($media->venue_profile_id === $venueProfile->id, 404);

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('status', 'media-removed');
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
