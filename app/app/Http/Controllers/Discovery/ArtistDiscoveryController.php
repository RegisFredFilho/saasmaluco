<?php

namespace App\Http\Controllers\Discovery;

use App\Enums\ArtistKind;
use App\Enums\PresentationType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Discovery\Concerns\RespondsWithDiscoveryPage;
use App\Models\ArtistProfile;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtistDiscoveryController extends Controller
{
    use RespondsWithDiscoveryPage;

    public function index(Request $request): View|Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'kind' => ['nullable', Rule::enum(ArtistKind::class)],
            'presentation_type' => ['nullable', Rule::enum(PresentationType::class)],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:24'],
        ]);

        $artists = ArtistProfile::query()
            ->discoverable()
            ->select([
                'id',
                'slug',
                'display_name',
                'stage_name',
                'kind',
                'city',
                'state',
                'region',
                'photo_path',
                'is_public',
            ])
            ->with('genres:id,name,slug')
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
            ->paginate($this->discoveryPerPage($request))
            ->withQueryString();

        return $this->discoveryResponse(
            $request,
            $artists,
            'discover.artists',
            'discover.partials.artist-items',
            'artists',
            [
                'filters' => $filters,
                'genres' => Genre::query()->orderBy('name')->get(['id', 'name']),
                'kinds' => ArtistKind::cases(),
                'presentationTypes' => PresentationType::cases(),
            ],
        );
    }
}
