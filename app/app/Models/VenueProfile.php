<?php

namespace App\Models;

use App\Enums\VenueEventType;
use App\Enums\VenueManagerRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VenueProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'display_name',
        'slug',
        'is_public',
        'cover_path',
        'description',
        'address_line',
        'neighborhood',
        'city',
        'state',
        'postal_code',
        'capacity',
        'event_type',
        'stage_structure',
        'equipment',
        'technical_info',
        'budget_min',
        'budget_max',
        'currency',
        'contact_name',
        'contact_email',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'event_type' => VenueEventType::class,
            'capacity' => 'integer',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VenueProfile $profile): void {
            if (blank($profile->slug)) {
                $profile->slug = static::uniqueSlug($profile->display_name);
            }
        });

        static::created(function (VenueProfile $profile): void {
            $profile->managers()->firstOrCreate(
                ['user_id' => $profile->owner_user_id],
                ['role' => VenueManagerRole::Owner],
            );
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function managers(): HasMany
    {
        return $this->hasMany(VenueManager::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'venue_genre')->withTimestamps();
    }

    public function media(): HasMany
    {
        return $this->hasMany(VenueMedia::class)->orderBy('sort_order');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function follows(): MorphMany
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
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

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
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
            filled($this->description),
            filled($this->city) && filled($this->state),
            filled($this->address_line) || filled($this->neighborhood),
            $this->capacity !== null,
            $this->genres()->exists(),
            filled($this->event_type),
            $this->budget_min !== null || $this->budget_max !== null,
            filled($this->contact_email) || filled($this->contact_phone),
            filled($this->cover_path) || $this->media()->exists(),
        ];

        $done = count(array_filter($checks));

        return (int) round(($done / count($checks)) * 100);
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'estabelecimento';
        $slug = $base;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
