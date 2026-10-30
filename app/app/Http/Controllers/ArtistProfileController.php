<?php

namespace App\Http\Controllers;

use App\Enums\ArtistKind;
use App\Enums\ArtistMediaType;
use App\Enums\PresentationType;
use App\Http\Requests\UpdateArtistProfileRequest;
use App\Models\ArtistAvailability;
use App\Models\ArtistExperience;
use App\Models\ArtistLink;
use App\Models\ArtistMedia;
use App\Models\ArtistProfile;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtistProfileController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ArtistProfile::class);

        $userId = $request->user()->id;

        $profiles = ArtistProfile::query()
            ->where(function ($query) use ($userId) {
                $query->where('owner_user_id', $userId)
                    ->orWhereHas('members', fn ($q) => $q->where('user_id', $userId));
            })
            ->withCount(['genres', 'media', 'experiences'])
            ->latest()
            ->get();

        return view('profiles.artists.index', compact('profiles'));
    }

    public function create(): View
    {
        $this->authorize('create', ArtistProfile::class);

        return view('profiles.artists.create', [
            'kinds' => ArtistKind::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        return redirect()
            ->route('artist-profiles.edit', $profile)
            ->with('status', 'artist-created');
    }

    public function show(ArtistProfile $artistProfile): View
    {
        $this->authorize('view', $artistProfile);

        $artistProfile->load([
            'genres',
            'media',
            'links',
            'experiences',
            'availabilities' => fn ($q) => $q->where('date', '>=', now()->toDateString())->limit(12),
            'reviews' => fn ($q) => $q->visible()->latest()->limit(20),
            'posts' => fn ($q) => $q->public()->latest()->limit(20),
        ])->loadCount(['follows', 'favorites']);

        $user = auth()->user();

        return view('profiles.artists.show', [
            'profile' => $artistProfile,
            'canManage' => $user ? $user->can('update', $artistProfile) : false,
            'isFollowing' => $user
                ? $artistProfile->follows()->where('user_id', $user->id)->exists()
                : false,
            'isFavorited' => $user
                ? $artistProfile->favorites()->where('user_id', $user->id)->exists()
                : false,
            'isOwn' => $user ? (int) $artistProfile->owner_user_id === (int) $user->id : false,
            'averageRating' => $artistProfile->averageRating(),
            'reviewsCount' => $artistProfile->reviews()->visible()->count(),
        ]);
    }

    public function edit(ArtistProfile $artistProfile): View
    {
        $this->authorize('update', $artistProfile);

        $artistProfile->load([
            'genres',
            'media',
            'links',
            'experiences',
            'availabilities',
            'members.user',
        ]);

        $pendingInvites = $artistProfile->memberInvites()
            ->pending()
            ->with('invitedUser')
            ->latest()
            ->get();

        return view('profiles.artists.edit', [
            'profile' => $artistProfile,
            'kinds' => ArtistKind::cases(),
            'presentationTypes' => PresentationType::cases(),
            'genres' => Genre::query()->orderBy('name')->get(),
            'pendingInvites' => $pendingInvites,
            'canManageMembers' => auth()->user()->can('manageMembers', $artistProfile),
        ]);
    }

    public function update(UpdateArtistProfileRequest $request, ArtistProfile $artistProfile): RedirectResponse
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

        $data['is_public'] = $request->boolean('is_public');

        $artistProfile->update($data);
        $artistProfile->genres()->sync($request->input('genre_ids', []));

        $this->syncLinks($artistProfile, $request->input('links', []));

        return redirect()
            ->route('artist-profiles.edit', $artistProfile)
            ->with('status', 'artist-updated');
    }

    public function storeMedia(Request $request, ArtistProfile $artistProfile): RedirectResponse
    {
        $this->authorize('manageMedia', $artistProfile);

        $data = $request->validate([
            'type' => ['required', Rule::enum(ArtistMediaType::class)],
            'photo' => ['required_if:type,photo', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'external_url' => [
                'required_if:type,video_link',
                'nullable',
                'url',
                'max:2048',
                'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\//i',
            ],
            'caption' => ['nullable', 'string', 'max:255'],
        ], [
            'external_url.regex' => 'No MVP, vídeos devem ser links do YouTube ou Vimeo.',
        ]);

        $type = ArtistMediaType::from($request->string('type')->toString());

        $payload = [
            'type' => $type,
            'caption' => $data['caption'] ?? null,
            'sort_order' => ((int) $artistProfile->media()->max('sort_order')) + 1,
        ];

        if ($type === ArtistMediaType::Photo) {
            $payload['path'] = $request->file('photo')->store('artists/portfolio', 'public');
            $payload['external_url'] = null;
        } else {
            $payload['external_url'] = $data['external_url'];
            $payload['path'] = null;
        }

        $artistProfile->media()->create($payload);

        return back()->with('status', 'media-added');
    }

    public function destroyMedia(ArtistProfile $artistProfile, ArtistMedia $media): RedirectResponse
    {
        $this->authorize('manageMedia', $artistProfile);
        abort_unless($media->artist_profile_id === $artistProfile->id, 404);

        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return back()->with('status', 'media-removed');
    }

    public function storeExperience(Request $request, ArtistProfile $artistProfile): RedirectResponse
    {
        $this->authorize('update', $artistProfile);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $artistProfile->experiences()->create([
            ...$data,
            'is_verified' => false,
            'source' => 'manual',
            'booking_id' => null,
        ]);

        return back()->with('status', 'experience-added');
    }

    public function destroyExperience(ArtistProfile $artistProfile, ArtistExperience $experience): RedirectResponse
    {
        $this->authorize('update', $artistProfile);
        abort_unless($experience->artist_profile_id === $artistProfile->id, 404);
        abort_unless($experience->isManual(), 403);

        $experience->delete();

        return back()->with('status', 'experience-removed');
    }

    public function storeAvailability(Request $request, ArtistProfile $artistProfile): RedirectResponse
    {
        $this->authorize('update', $artistProfile);

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'is_available' => ['sometimes', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        ArtistAvailability::query()->updateOrCreate(
            [
                'artist_profile_id' => $artistProfile->id,
                'date' => $data['date'],
            ],
            [
                'is_available' => $request->boolean('is_available', true),
                'note' => $data['note'] ?? null,
            ],
        );

        return back()->with('status', 'availability-saved');
    }

    public function destroyAvailability(ArtistProfile $artistProfile, ArtistAvailability $availability): RedirectResponse
    {
        $this->authorize('update', $artistProfile);
        abort_unless($availability->artist_profile_id === $artistProfile->id, 404);

        $availability->delete();

        return back()->with('status', 'availability-removed');
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
