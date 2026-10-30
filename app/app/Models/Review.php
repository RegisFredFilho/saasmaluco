<?php

namespace App\Models;

use App\Enums\ReviewAuthorSide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    public const WINDOW_DAYS = 30;

    protected $fillable = [
        'booking_id',
        'author_user_id',
        'author_side',
        'rateable_type',
        'rateable_id',
        'rating',
        'comment',
        'is_hidden',
        'hidden_reason',
        'hidden_by_user_id',
        'hidden_at',
    ];

    protected function casts(): array
    {
        return [
            'author_side' => ReviewAuthorSide::class,
            'rating' => 'integer',
            'is_hidden' => 'boolean',
            'hidden_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by_user_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }
}
