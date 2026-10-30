<?php

namespace App\Http\Controllers\Discovery;

use App\Enums\VenueEventType;
use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\VenueProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VenueDiscoveryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'event_type' => ['nullable', Rule::enum(VenueEventType::class)],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
        ]);

        $venues = VenueProfile::query()
            ->discoverable()
            ->with('genres')
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
            ->paginate(12)
            ->withQueryString();

        return view('discover.venues', [
            'venues' => $venues,
            'filters' => $filters,
            'genres' => Genre::query()->orderBy('name')->get(),
            'eventTypes' => VenueEventType::cases(),
        ]);
    }
}
