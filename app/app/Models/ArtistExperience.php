<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistExperience extends Model
{
    protected $fillable = [
        'artist_profile_id',
        'title',
        'venue_name',
        'city',
        'started_on',
        'ended_on',
        'description',
        'is_verified',
        'source',
        'booking_id',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'is_verified' => 'boolean',
        ];
    }

    public function artistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isManual(): bool
    {
        return $this->source === 'manual';
    }
}
