<?php

namespace App\Models;

use App\Enums\ArtistInviteStatus;
use App\Enums\ArtistMemberRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistMemberInvite extends Model
{
    protected $fillable = [
        'artist_profile_id',
        'invited_user_id',
        'invited_by_user_id',
        'role',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => ArtistMemberRole::class,
            'status' => ArtistInviteStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    public function artistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class);
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ArtistInviteStatus::Pending);
    }
}
