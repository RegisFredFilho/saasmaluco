<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'publisher_type',
        'publisher_id',
        'body',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function publisher(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function publisherLabel(): string
    {
        $publisher = $this->publisher;

        if ($publisher instanceof ArtistProfile) {
            return $publisher->publicName();
        }

        if ($publisher instanceof VenueProfile) {
            return $publisher->display_name;
        }

        if ($publisher instanceof User) {
            return $publisher->name;
        }

        return 'Publicação';
    }

    public function publisherUrl(): ?string
    {
        $publisher = $this->publisher;

        if ($publisher instanceof ArtistProfile) {
            return route('artists.show', $publisher);
        }

        if ($publisher instanceof VenueProfile) {
            return route('venues.show', $publisher);
        }

        return null;
    }
}
