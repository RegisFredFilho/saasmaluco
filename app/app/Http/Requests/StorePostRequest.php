<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isActive();
    }

    protected function prepareForValidation(): void
    {
        $key = (string) $this->input('publisher_key', '');

        if ($key === 'user') {
            $this->merge([
                'publisher_type' => 'user',
                'publisher_id' => null,
            ]);

            return;
        }

        if (str_starts_with($key, 'artist:')) {
            $this->merge([
                'publisher_type' => 'artist',
                'publisher_id' => (int) substr($key, strlen('artist:')),
            ]);

            return;
        }

        if (str_starts_with($key, 'venue:')) {
            $this->merge([
                'publisher_type' => 'venue',
                'publisher_id' => (int) substr($key, strlen('venue:')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:5000'],
            'publisher_key' => ['required', 'string'],
            'publisher_type' => ['required', Rule::in(['user', 'artist', 'venue'])],
            'publisher_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => in_array($this->input('publisher_type'), ['artist', 'venue'], true)),
            ],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
