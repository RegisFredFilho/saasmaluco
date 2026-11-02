<?php

namespace App\Http\Resources;

use App\Models\VenueProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin VenueProfile */
class VenueResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'city' => $this->city,
            'state' => $this->state,
            'description' => $this->description,
            'is_public' => $this->is_public,
            'photo_url' => $this->photoUrl(),
            'cover_url' => $this->coverUrl(),
            'genres' => $this->whenLoaded('genres', fn () => $this->genres->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'slug' => $g->slug,
            ])),
        ];
    }
}
