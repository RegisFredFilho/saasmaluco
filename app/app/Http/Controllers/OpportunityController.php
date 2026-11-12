<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\OpportunityStatus;
use App\Models\Application;
use App\Models\ArtistProfile;
use App\Models\Opportunity;
use App\Models\VenueProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function show(Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        $opportunity->load(['venueProfile', 'genres'])->loadCount([
            'applications',
            'applications as accepted_applications_count' => fn ($q) => $q->where('status', ApplicationStatus::Accepted),
        ]);

        $user = auth()->user();
        $canManage = $user ? $user->can('update', $opportunity) : false;

        $manageableArtists = collect();
        $myApplications = collect();

        if ($user) {
            $manageableArtists = ArtistProfile::query()
                ->where(function ($query) use ($user) {
                    $query->where('owner_user_id', $user->id)
                        ->orWhereHas('members', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->orderBy('display_name')
                ->get()
                ->filter(fn (ArtistProfile $artist) => $user->can('update', $artist))
                ->values();

            if ($manageableArtists->isNotEmpty()) {
                $myApplications = Application::query()
                    ->where('opportunity_id', $opportunity->id)
                    ->whereIn('artist_profile_id', $manageableArtists->pluck('id'))
                    ->with(['artistProfile', 'conversation'])
                    ->get();
            }
        }

        $applications = collect();
        if ($canManage) {
            $applications = $opportunity->applications()
                ->with(['artistProfile', 'submittedBy', 'conversation'])
                ->latest()
                ->get();
        }

        $appliedArtistIds = $myApplications->pluck('artist_profile_id')->all();
        $eligibleArtists = $manageableArtists
            ->reject(fn (ArtistProfile $artist) => in_array($artist->id, $appliedArtistIds, true))
            ->values();

        return view('opportunities.show', [
            'opportunity' => $opportunity,
            'canManage' => $canManage,
            'canApply' => $user
                ? $user->can('create', [Application::class, $opportunity])
                : false,
            'eligibleArtists' => $eligibleArtists,
            'myApplications' => $myApplications,
            'applications' => $applications,
            'applicationStatuses' => ApplicationStatus::cases(),
        ]);
    }

    public function store(Request $request, VenueProfile $venueProfile): RedirectResponse
    {
        $this->authorize('create', [Opportunity::class, $venueProfile]);

        $data = $this->validated($request);
        $status = OpportunityStatus::from($data['status'] ?? OpportunityStatus::Draft->value);

        $opportunity = $venueProfile->opportunities()->create([
            ...collect($data)->except(['genre_ids', 'status'])->all(),
            'created_by_user_id' => $request->user()->id,
            'status' => $status,
            'currency' => 'BRL',
        ]);

        $opportunity->genres()->sync($request->input('genre_ids', []));

        return redirect()
            ->route('venue-profiles.edit', $venueProfile)
            ->with('status', 'opportunity-created');
    }

    public function update(Request $request, VenueProfile $venueProfile, Opportunity $opportunity): RedirectResponse
    {
        abort_unless($opportunity->venue_profile_id === $venueProfile->id, 404);
        $this->authorize('update', $opportunity);

        $data = $this->validated($request);
        $nextStatus = OpportunityStatus::from($data['status']);

        if ($nextStatus !== $opportunity->status && ! $opportunity->canTransitionTo($nextStatus)) {
            return back()
                ->withErrors(['status' => 'Transição de status não permitida.'])
                ->withInput();
        }

        $opportunity->update([
            ...collect($data)->except(['genre_ids', 'status'])->all(),
            'status' => $nextStatus,
        ]);

        $opportunity->genres()->sync($request->input('genre_ids', []));

        return redirect()
            ->route('venue-profiles.edit', $venueProfile)
            ->with('status', 'opportunity-updated');
    }

    public function updateStatus(Request $request, VenueProfile $venueProfile, Opportunity $opportunity): RedirectResponse
    {
        abort_unless($opportunity->venue_profile_id === $venueProfile->id, 404);
        $this->authorize('update', $opportunity);

        $data = $request->validate([
            'status' => ['required', Rule::enum(OpportunityStatus::class)],
        ]);

        $nextStatus = OpportunityStatus::from($data['status']);

        if ($nextStatus !== $opportunity->status && ! $opportunity->canTransitionTo($nextStatus)) {
            return back()
                ->withErrors(['status' => 'Transição de status não permitida.'])
                ->withInput();
        }

        $opportunity->update(['status' => $nextStatus]);

        return redirect()
            ->route('venue-profiles.edit', $venueProfile)
            ->with('status', 'opportunity-status-updated');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:8000'],
            'event_date' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'ends_at' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'location_label' => ['nullable', 'string', 'max:255'],
            'budget_min' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'gte:budget_min'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'available_structure' => ['nullable', 'string', 'max:5000'],
            'slots_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'application_deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(OpportunityStatus::class)],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ]);
    }
}
