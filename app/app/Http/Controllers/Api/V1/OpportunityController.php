<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OpportunityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'event_from' => ['nullable', 'date'],
            'event_to' => ['nullable', 'date', 'after_or_equal:event_from'],
        ]);

        $opportunities = Opportunity::query()
            ->discoverable()
            ->with(['venueProfile', 'genres'])
            ->when($filters['q'] ?? null, function ($query, $q) {
                $term = '%'.$q.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('location_label', 'like', $term);
                });
            })
            ->when($filters['city'] ?? null, function ($query, $city) {
                $query->whereHas('venueProfile', fn ($q) => $q->where('city', 'like', $city.'%'));
            })
            ->when($filters['state'] ?? null, function ($query, $state) {
                $query->whereHas('venueProfile', fn ($q) => $q->where('state', $state));
            })
            ->when($filters['genre_id'] ?? null, function ($query, $genreId) {
                $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
            })
            ->when($filters['event_from'] ?? null, fn ($query, $from) => $query->whereDate('event_date', '>=', $from))
            ->when($filters['event_to'] ?? null, fn ($query, $to) => $query->whereDate('event_date', '<=', $to))
            ->orderBy('event_date')
            ->orderBy('title')
            ->paginate(12);

        return OpportunityResource::collection($opportunities);
    }

    public function show(Opportunity $opportunity): OpportunityResource
    {
        $this->authorize('view', $opportunity);

        $opportunity->load(['venueProfile', 'genres']);

        return OpportunityResource::make($opportunity);
    }
}
