<?php

namespace App\Http\Controllers\Discovery;

use App\Enums\ArtistKind;
use App\Enums\PresentationType;
use App\Http\Controllers\Controller;
use App\Models\ArtistProfile;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtistDiscoveryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'kind' => ['nullable', Rule::enum(ArtistKind::class)],
            'presentation_type' => ['nullable', Rule::enum(PresentationType::class)],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
        ]);

        $artists = ArtistProfile::query()
            ->discoverable()
            ->with('genres')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $term = '%'.$q.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('display_name', 'like', $term)
                        ->orWhere('stage_name', 'like', $term);
                });
            })
            ->when($filters['city'] ?? null, fn ($query, $city) => $query->where('city', 'like', $city.'%'))
            ->when($filters['state'] ?? null, fn ($query, $state) => $query->where('state', $state))
            ->when($filters['region'] ?? null, fn ($query, $region) => $query->where('region', 'like', $region.'%'))
            ->when($filters['kind'] ?? null, fn ($query, $kind) => $query->where('kind', $kind))
            ->when($filters['presentation_type'] ?? null, fn ($query, $type) => $query->where('presentation_type', $type))
            ->when($filters['genre_id'] ?? null, function ($query, $genreId) {
                $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
            })
            ->orderBy('display_name')
            ->paginate(12)
            ->withQueryString();

        return view('discover.artists', [
            'artists' => $artists,
            'filters' => $filters,
            'genres' => Genre::query()->orderBy('name')->get(),
            'kinds' => ArtistKind::cases(),
            'presentationTypes' => PresentationType::cases(),
        ]);
    }
}
