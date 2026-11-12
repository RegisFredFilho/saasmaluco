<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MosaicGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'grid_size',
    ];

    protected function casts(): array
    {
        return [
            'grid_size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absolutePath(): string
    {
        return Storage::disk('public')->path($this->image_path);
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk('public')->exists($this->image_path);
    }
}
