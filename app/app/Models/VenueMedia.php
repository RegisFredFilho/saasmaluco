<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VenueMedia extends Model
{
    protected $table = 'venue_media';

    protected $fillable = [
        'venue_profile_id',
        'path',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function venueProfile(): BelongsTo
    {
        return $this->belongsTo(VenueProfile::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
