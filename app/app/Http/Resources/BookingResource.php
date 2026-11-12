<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Booking */
class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'artist_completed_at' => $this->artist_completed_at,
            'venue_completed_at' => $this->venue_completed_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason,
            'opportunity' => OpportunityResource::make($this->whenLoaded('opportunity')),
            'artist' => ArtistResource::make($this->whenLoaded('artistProfile')),
            'venue' => VenueResource::make($this->whenLoaded('venueProfile')),
        ];
    }
}
