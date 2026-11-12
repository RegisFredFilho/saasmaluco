<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'proposal_id',
        'conversation_id',
        'application_id',
        'opportunity_id',
        'artist_profile_id',
        'venue_profile_id',
        'amount',
        'currency',
        'conditions',
        'starts_at',
        'ends_at',
        'timezone',
        'status',
        'artist_completed_at',
        'venue_completed_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => BookingStatus::class,
            'artist_completed_at' => 'datetime',
            'venue_completed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function artistProfile(): BelongsTo
    {
        return $this->belongsTo(ArtistProfile::class);
    }

    public function venueProfile(): BelongsTo
    {
        return $this->belongsTo(VenueProfile::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function experience(): HasOne
    {
        return $this->hasOne(ArtistExperience::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeBlockingAgenda(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Confirmed);
    }

    public static function hasConflict(int $artistProfileId, Carbon $startsAt, Carbon $endsAt, ?int $ignoreId = null): bool
    {
        return static::query()
            ->blockingAgenda()
            ->where('artist_profile_id', $artistProfileId)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function windowFromOpportunity(Opportunity $opportunity): array
    {
        $date = $opportunity->event_date?->format('Y-m-d') ?? now()->addWeek()->format('Y-m-d');
        $startTime = $opportunity->starts_at
            ? substr((string) $opportunity->starts_at, 0, 8)
            : '20:00:00';
        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }

        $startsAt = Carbon::parse($date.' '.$startTime);

        if ($opportunity->ends_at) {
            $endTime = substr((string) $opportunity->ends_at, 0, 8);
            if (strlen($endTime) === 5) {
                $endTime .= ':00';
            }
            $endsAt = Carbon::parse($date.' '.$endTime);
            if ($endsAt->lte($startsAt)) {
                $endsAt->addDay();
            }
        } elseif ($opportunity->duration_minutes) {
            $endsAt = $startsAt->copy()->addMinutes((int) $opportunity->duration_minutes);
        } else {
            $endsAt = $startsAt->copy()->addHours(2);
        }

        return [$startsAt, $endsAt];
    }

    public function markCompletedBy(User $user): void
    {
        $isArtist = $user->can('update', $this->artistProfile);
        $isVenue = $user->can('manageOpportunities', $this->venueProfile);

        if ($isArtist && $this->artist_completed_at === null) {
            $this->artist_completed_at = now();
        }

        if ($isVenue && $this->venue_completed_at === null) {
            $this->venue_completed_at = now();
        }

        $this->save();

        if ($this->artist_completed_at && $this->venue_completed_at && $this->status === BookingStatus::Confirmed) {
            $this->finalizeCompletion();
        }
    }

    public function finalizeCompletion(): void
    {
        $this->update([
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);

        if ($this->experience()->exists()) {
            return;
        }

        $this->loadMissing(['opportunity', 'venueProfile', 'artistProfile']);

        ArtistExperience::query()->create([
            'artist_profile_id' => $this->artist_profile_id,
            'title' => $this->opportunity->title,
            'venue_name' => $this->venueProfile->display_name,
            'city' => $this->venueProfile->city,
            'started_on' => $this->starts_at->toDateString(),
            'ended_on' => $this->ends_at->toDateString(),
            'description' => 'Show concluído via SindiGig (booking #'.$this->id.').',
            'is_verified' => true,
            'source' => 'booking',
            'booking_id' => $this->id,
        ]);
    }
}
