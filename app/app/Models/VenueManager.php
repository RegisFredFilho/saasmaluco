<?php

namespace App\Models;

use App\Enums\VenueManagerRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueManager extends Model
{
    protected $fillable = [
        'venue_profile_id',
        'user_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => VenueManagerRole::class,
        ];
    }

    public function venueProfile(): BelongsTo
    {
        return $this->belongsTo(VenueProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
