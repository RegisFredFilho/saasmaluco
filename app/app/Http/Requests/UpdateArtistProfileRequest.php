<?php

namespace App\Http\Requests;

use App\Enums\ArtistKind;
use App\Enums\PresentationType;
use App\Models\ArtistProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArtistProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ArtistProfile|null $profile */
        $profile = $this->route('artist_profile')
            ?? $this->route('artistProfile')
            ?? $this->route('artist');

        return $profile instanceof ArtistProfile
            && ($this->user()?->can('update', $profile) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'stage_name' => ['nullable', 'string', 'max:255'],
            'kind' => ['required', Rule::enum(ArtistKind::class)],
            'is_public' => ['sometimes', 'boolean'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'presentation_type' => ['nullable', Rule::enum(PresentationType::class)],
            'members_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'fee_min' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'fee_max' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'gte:fee_min'],
            'typical_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:600'],
            'own_equipment' => ['nullable', 'string', 'max:5000'],
            'required_tech' => ['nullable', 'string', 'max:5000'],
            'repertoire' => ['nullable', 'string', 'max:10000'],
            'availability_notes' => ['nullable', 'string', 'max:5000'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'links' => ['nullable', 'array', 'max:15'],
            'links.*.label' => ['required_with:links.*.url', 'string', 'max:120'],
            'links.*.url' => ['required_with:links.*.label', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'fee_max.gte' => 'O cachê máximo deve ser maior ou igual ao mínimo.',
            'links.*.url.url' => 'Informe uma URL válida (incluindo https://).',
        ];
    }
}
