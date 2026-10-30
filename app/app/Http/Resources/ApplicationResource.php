<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Application */
class ApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'message' => $this->message,
            'venue_notes' => $this->venue_notes,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
            'artist' => ArtistResource::make($this->whenLoaded('artistProfile')),
            'opportunity' => OpportunityResource::make($this->whenLoaded('opportunity')),
            'conversation_id' => $this->when(
                $this->relationLoaded('conversation'),
                fn () => $this->conversation?->id,
            ),
        ];
    }
}
