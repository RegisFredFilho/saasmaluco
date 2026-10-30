<?php

namespace App\Models;

use App\Enums\ArtistMediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ArtistMedia extends Model
{
    protected $table = 'artist_media';

    protected $fillable = [
        'artist_profile_id',
        'type',
        'path',
        'external_url',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ArtistMediaType::class,
            'sort_order' => 'integer',
        ];
    }

    public function artistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class);
    }

    public function url(): ?string
    {
        if ($this->type === ArtistMediaType::VideoLink) {
            return $this->external_url;
        }

        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }
}
