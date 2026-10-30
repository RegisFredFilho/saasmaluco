<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Proposal */
class ProposalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'conditions' => $this->conditions,
            'valid_until' => $this->valid_until,
            'status' => $this->status?->value,
            'version' => $this->version,
            'supersedes_id' => $this->supersedes_id,
            'superseded_by_id' => $this->superseded_by_id,
            'created_by_user_id' => $this->created_by_user_id,
            'responded_at' => $this->responded_at,
            'created_at' => $this->created_at,
            'booking_id' => $this->whenLoaded('booking', fn () => $this->booking?->id),
        ];
    }
}
