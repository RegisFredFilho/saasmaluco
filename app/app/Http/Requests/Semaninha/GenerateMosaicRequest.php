<?php

namespace App\Http\Requests\Semaninha;

use Illuminate\Foundation\Http\FormRequest;

class GenerateMosaicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'size:9'],
            'images.*' => ['required', 'url'],
            'grid' => ['sometimes', 'integer', 'in:3,4'],
        ];
    }
}
