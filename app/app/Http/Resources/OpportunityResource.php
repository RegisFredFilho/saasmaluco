<?php

namespace App\Http\Resources;

use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Opportunity */
class OpportunityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status?->value,
            'event_date' => $this->event_date?->toDateString(),
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'location_label' => $this->location_label,
            'slots_count' => $this->slots_count,
            'description' => $this->description,
            'venue' => VenueResource::make($this->whenLoaded('venueProfile')),
            'genres' => $this->whenLoaded('genres', fn () => $this->genres->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'slug' => $g->slug,
            ])),
        ];
    }
}
