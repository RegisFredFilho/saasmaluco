<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'application_id',
        'opportunity_id',
        'venue_profile_id',
        'artist_profile_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function venueProfile(): BelongsTo
    {
        return $this->belongsTo(VenueProfile::class);
    }

    public function artistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public static function openForApplication(Application $application): self
    {
        $application->loadMissing(['opportunity']);

        return static::query()->firstOrCreate(
            ['application_id' => $application->id],
            [
                'opportunity_id' => $application->opportunity_id,
                'venue_profile_id' => $application->opportunity->venue_profile_id,
                'artist_profile_id' => $application->artist_profile_id,
            ],
        );
    }
}
