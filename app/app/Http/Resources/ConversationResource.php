<?php

namespace App\Http\Resources;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Conversation */
class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'opportunity' => OpportunityResource::make($this->whenLoaded('opportunity')),
            'artist' => ArtistResource::make($this->whenLoaded('artistProfile')),
            'venue' => VenueResource::make($this->whenLoaded('venueProfile')),
            'application_id' => $this->application_id,
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'proposals' => ProposalResource::collection($this->whenLoaded('proposals')),
        ];
    }
}
