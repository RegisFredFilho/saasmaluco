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
            'photo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'mimetypes:image/jpeg,image/jpg,image/pjpeg,image/png,image/webp', 'max:5120'],
            'cover' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'mimetypes:image/jpeg,image/jpg,image/pjpeg,image/png,image/webp', 'max:5120'],
            'links' => ['nullable', 'array', 'max:15'],
            'links.*.label' => ['nullable', 'required_with:links.*.url', 'string', 'max:120'],
            'links.*.url' => ['nullable', 'required_with:links.*.label', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'fee_max.gte' => 'O cachê máximo deve ser maior ou igual ao mínimo.',
            'links.*.label.required_with' => 'Informe o rótulo do link.',
            'links.*.label.string' => 'Informe o rótulo do link.',
            'links.*.url.required_with' => 'Informe a URL do link.',
            'links.*.url.url' => 'Informe uma URL válida (incluindo https://).',
            'photo.image' => 'A foto de perfil precisa ser JPG, JPEG, PNG ou WebP.',
            'photo.mimes' => 'A foto de perfil precisa ser JPG, JPEG, PNG ou WebP.',
            'photo.mimetypes' => 'A foto de perfil precisa ser JPG, JPEG, PNG ou WebP.',
            'photo.max' => 'A foto de perfil deve ter no máximo 5 MB.',
            'cover.image' => 'A capa precisa ser JPG, JPEG, PNG ou WebP.',
            'cover.mimes' => 'A capa precisa ser JPG, JPEG, PNG ou WebP.',
            'cover.mimetypes' => 'A capa precisa ser JPG, JPEG, PNG ou WebP.',
            'cover.max' => 'A capa deve ter no máximo 5 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $links = collect($this->input('links', []))
            ->map(function ($link): array {
                $label = trim((string) ($link['label'] ?? ''));
                $url = trim((string) ($link['url'] ?? ''));
                if (in_array($url, ['http://', 'https://'], true)) {
                    $url = '';
                }

                return ['label' => $label, 'url' => $url];
            })
            ->filter(fn (array $link): bool => $link['label'] !== '' || $link['url'] !== '')
            ->values()
            ->all();

        $this->merge(['links' => $links]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach (['photo', 'cover'] as $field) {
                $file = $this->file($field);
                if ($file && ! $file->isValid()) {
                    $validator->errors()->add(
                        $field,
                        'Não foi possível enviar a imagem. Use JPEG, PNG ou WebP de até 5 MB.',
                    );
                }
            }
        });
    }
}
