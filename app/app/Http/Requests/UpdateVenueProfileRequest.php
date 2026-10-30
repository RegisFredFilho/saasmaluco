<?php

namespace App\Http\Requests;

use App\Enums\VenueEventType;
use App\Models\VenueProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVenueProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var VenueProfile|null $profile */
        $profile = $this->route('venue_profile')
            ?? $this->route('venueProfile')
            ?? $this->route('venue');

        return $profile instanceof VenueProfile
            && ($this->user()?->can('update', $profile) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:8000'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500000'],
            'event_type' => ['nullable', Rule::enum(VenueEventType::class)],
            'stage_structure' => ['nullable', 'string', 'max:5000'],
            'equipment' => ['nullable', 'string', 'max:5000'],
            'technical_info' => ['nullable', 'string', 'max:5000'],
            'budget_min' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'gte:budget_min'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'manager_emails' => ['nullable', 'array', 'max:10'],
            'manager_emails.*' => ['nullable', 'email', 'max:255'],
        ];
    }
}
