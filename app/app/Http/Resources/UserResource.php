<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'avatar_url' => $this->avatarUrl(),
            'email_verified_at' => $this->email_verified_at,
            'is_platform_admin' => $this->isPlatformAdmin(),
            'is_active' => $this->isActive(),
            'deactivated_at' => $this->deactivated_at,
            'deactivation_reason' => $this->when(
                $request->user()?->isPlatformAdmin(),
                $this->deactivation_reason,
            ),
        ];
    }
}
