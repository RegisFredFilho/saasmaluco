<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\OpportunityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    protected $fillable = [
        'venue_profile_id',
        'created_by_user_id',
        'title',
        'description',
        'event_date',
        'starts_at',
        'ends_at',
        'location_label',
        'budget_min',
        'budget_max',
        'currency',
        'duration_minutes',
        'available_structure',
        'slots_count',
        'application_deadline',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'application_deadline' => 'date',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'duration_minutes' => 'integer',
            'slots_count' => 'integer',
            'status' => OpportunityStatus::class,
        ];
    }

    public function venueProfile(): BelongsTo
    {
        return $this->belongsTo(VenueProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'opportunity_genre')->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function canTransitionTo(OpportunityStatus $next): bool
    {
        return in_array($next, $this->status->allowedTransitions(), true);
    }

    /**
     * D7 (hard): open + prazo vigente + vagas de aceites disponíveis.
     */
    public function acceptsNewApplications(): bool
    {
        if (! $this->status->acceptsApplications()) {
            return false;
        }

        if ($this->application_deadline !== null && $this->application_deadline->lt(now()->startOfDay())) {
            return false;
        }

        $accepted = $this->applications()
            ->where('status', ApplicationStatus::Accepted)
            ->count();

        return $accepted < max(1, (int) $this->slots_count);
    }

    public function syncFilledIfSlotsComplete(): void
    {
        if ($this->status !== OpportunityStatus::Open) {
            return;
        }

        $accepted = $this->applications()
            ->where('status', ApplicationStatus::Accepted)
            ->count();

        if ($accepted >= max(1, (int) $this->slots_count) && $this->canTransitionTo(OpportunityStatus::Filled)) {
            $this->update(['status' => OpportunityStatus::Filled]);
        }
    }

    /**
     * D17: abertas e vinculadas a estabelecimento descoberta.
     */
    public function scopeDiscoverable(Builder $query): Builder
    {
        return $query
            ->where('status', OpportunityStatus::Open)
            ->whereHas('venueProfile', fn (Builder $q) => $q->discoverable());
    }
}
