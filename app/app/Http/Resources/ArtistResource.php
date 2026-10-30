<?php

namespace App\Http\Resources;

use App\Models\ArtistProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ArtistProfile */
class ArtistResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'display_name' => $this->publicName(),
            'stage_name' => $this->stage_name,
            'kind' => $this->kind?->value,
            'presentation_type' => $this->presentation_type?->value,
            'city' => $this->city,
            'state' => $this->state,
            'region' => $this->region,
            'bio' => $this->bio,
            'is_public' => $this->is_public,
            'photo_url' => $this->photoUrl(),
            'cover_url' => $this->coverUrl(),
            'genres' => $this->whenLoaded('genres', fn () => $this->genres->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'slug' => $g->slug,
            ])),
            'follows_count' => $this->whenCounted('follows'),
            'favorites_count' => $this->whenCounted('favorites'),
            'average_rating' => $this->whenHas('average_rating'),
        ];
    }
}
