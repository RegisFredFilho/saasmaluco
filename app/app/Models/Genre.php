<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Genre extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::creating(function (Genre $genre): void {
            if (blank($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });
    }

    public function artistProfiles(): BelongsToMany
    {
        return $this->belongsToMany(ArtistProfile::class, 'artist_genre')->withTimestamps();
    }

    public function venueProfiles(): BelongsToMany
    {
        return $this->belongsToMany(VenueProfile::class, 'venue_genre')->withTimestamps();
    }
}
