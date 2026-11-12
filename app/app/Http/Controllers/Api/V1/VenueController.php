<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\VenueProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VenueController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
        ]);

        $venues = VenueProfile::query()
            ->discoverable()
            ->with('genres')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $term = '%'.$q.'%';
                $query->where('display_name', 'like', $term);
            })
            ->when($filters['city'] ?? null, fn ($query, $city) => $query->where('city', 'like', $city.'%'))
            ->when($filters['state'] ?? null, fn ($query, $state) => $query->where('state', $state))
            ->when($filters['genre_id'] ?? null, function ($query, $genreId) {
                $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
            })
            ->orderBy('display_name')
            ->paginate(12);

        return VenueResource::collection($venues);
    }

    public function show(VenueProfile $venue): VenueResource
    {
        $this->authorize('view', $venue);

        $venue->load('genres');

        return VenueResource::make($venue);
    }
}
