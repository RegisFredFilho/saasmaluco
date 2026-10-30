<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'avatar_path',
    'city',
    'state',
    'country',
    'preferences',
])]
#[Hidden(['password', 'remember_token', 'is_platform_admin', 'deactivation_reason'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_platform_admin' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public function ownedArtistProfiles(): HasMany
    {
        return $this->hasMany(ArtistProfile::class, 'owner_user_id');
    }

    public function ownedVenueProfiles(): HasMany
    {
        return $this->hasMany(VenueProfile::class, 'owner_user_id');
    }

    public function artistMemberships(): HasMany
    {
        return $this->hasMany(ArtistMember::class);
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'publisher');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }
}
