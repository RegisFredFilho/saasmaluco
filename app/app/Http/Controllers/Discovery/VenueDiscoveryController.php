<?php

namespace App\Http\Controllers\Discovery;

use App\Enums\VenueEventType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Discovery\Concerns\RespondsWithDiscoveryPage;
use App\Models\Genre;
use App\Models\VenueProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VenueDiscoveryController extends Controller
{
    use RespondsWithDiscoveryPage;

    public function index(Request $request): View|Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'event_type' => ['nullable', Rule::enum(VenueEventType::class)],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:24'],
        ]);

        $venues = VenueProfile::query()
            ->discoverable()
            ->select([
                'id',
                'slug',
                'display_name',
                'city',
                'state',
                'event_type',
                'capacity',
                'cover_path',
                'is_public',
            ])
            ->with('genres:id,name,slug')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where('display_name', 'like', '%'.$q.'%');
            })
            ->when($filters['city'] ?? null, fn ($query, $city) => $query->where('city', 'like', $city.'%'))
            ->when($filters['state'] ?? null, fn ($query, $state) => $query->where('state', $state))
            ->when($filters['event_type'] ?? null, fn ($query, $type) => $query->where('event_type', $type))
            ->when($filters['genre_id'] ?? null, function ($query, $genreId) {
                $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
            })
            ->orderBy('display_name')
            ->paginate($this->discoveryPerPage($request))
            ->withQueryString();

        return $this->discoveryResponse(
            $request,
            $venues,
            'discover.venues',
            'discover.partials.venue-items',
            'venues',
            [
                'filters' => $filters,
                'genres' => Genre::query()->orderBy('name')->get(['id', 'name']),
                'eventTypes' => VenueEventType::cases(),
            ],
        );
    }
}
