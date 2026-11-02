<?php

namespace App\Models;

use App\Enums\ArtistKind;
use App\Enums\ArtistMemberRole;
use App\Enums\PresentationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtistProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'display_name',
        'stage_name',
        'slug',
        'kind',
        'is_public',
        'photo_path',
        'cover_path',
        'bio',
        'city',
        'state',
        'region',
        'presentation_type',
        'members_count',
        'fee_min',
        'fee_max',
        'currency',
        'typical_duration_minutes',
        'own_equipment',
        'required_tech',
        'repertoire',
        'availability_notes',
    ];

    protected function casts(): array
    {
        return [
            'kind' => ArtistKind::class,
            'presentation_type' => PresentationType::class,
            'is_public' => 'boolean',
            'members_count' => 'integer',
            'fee_min' => 'decimal:2',
            'fee_max' => 'decimal:2',
            'typical_duration_minutes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ArtistProfile $profile): void {
            if (blank($profile->slug)) {
                $profile->slug = static::uniqueSlug($profile->display_name);
            }
        });

        static::created(function (ArtistProfile $profile): void {
            $profile->members()->firstOrCreate(
                ['user_id' => $profile->owner_user_id],
                ['role' => ArtistMemberRole::Owner],
            );
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ArtistMember::class);
    }

    public function memberInvites(): HasMany
    {
        return $this->hasMany(ArtistMemberInvite::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'artist_genre')->withTimestamps();
    }

    public function media(): HasMany
    {
        return $this->hasMany(ArtistMedia::class)->orderBy('sort_order');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ArtistLink::class)->orderBy('sort_order');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(ArtistExperience::class)->orderByDesc('started_on');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ArtistAvailability::class)->orderBy('date');
    }

    public function follows(): MorphMany
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'publisher');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'rateable');
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->visible()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function publicName(): string
    {
        return $this->stage_name ?: $this->display_name;
    }

    /**
     * D17: perfis públicos entram na listagem; cidade/UF são filtros opcionais.
     */
    public function scopeDiscoverable(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function completionPercent(): int
    {
        $checks = [
            filled($this->bio),
            filled($this->city) && filled($this->state),
            $this->genres()->exists(),
            filled($this->presentation_type),
            $this->fee_min !== null || $this->fee_max !== null,
            filled($this->photo_path),
            $this->links()->exists() || $this->media()->exists(),
            $this->experiences()->exists() || filled($this->availability_notes),
        ];

        $done = count(array_filter($checks));

        return (int) round(($done / count($checks)) * 100);
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'artista';
        $slug = $base;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
