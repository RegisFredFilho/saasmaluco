<?php

namespace App\Models;

use App\Enums\ArtistMemberRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistMember extends Model
{
    protected $fillable = [
        'artist_profile_id',
        'user_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => ArtistMemberRole::class,
        ];
    }

    public function artistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
