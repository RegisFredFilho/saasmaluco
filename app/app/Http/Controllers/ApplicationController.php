<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ArtistProfile;
use App\Models\Conversation;
use App\Models\Opportunity;
use App\Services\InAppNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);

        $userId = $request->user()->id;

        $applications = Application::query()
            ->where(function ($query) use ($userId) {
                $query->where('submitted_by_user_id', $userId)
                    ->orWhereHas('artistProfile', function ($q) use ($userId) {
                        $q->where('owner_user_id', $userId)
                            ->orWhereHas('members', fn ($m) => $m->where('user_id', $userId));
                    });
            })
            ->with(['opportunity.venueProfile', 'artistProfile'])
            ->latest()
            ->paginate(20);

        return view('applications.index', compact('applications'));
    }

    public function store(Request $request, Opportunity $opportunity, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('create', [Application::class, $opportunity]);

        $data = $request->validate([
            'artist_profile_id' => ['required', 'integer', 'exists:artist_profiles,id'],
            'message' => ['nullable', 'string', 'max:4000'],
        ]);

        /** @var ArtistProfile $artist */
        $artist = ArtistProfile::query()->findOrFail($data['artist_profile_id']);
        $this->authorize('update', $artist);

        if (! $opportunity->acceptsNewApplications()) {
            return back()->withErrors([
                'artist_profile_id' => 'Esta oportunidade não está aceitando candidaturas.',
            ]);
        }

        $exists = Application::query()
            ->where('opportunity_id', $opportunity->id)
            ->where('artist_profile_id', $artist->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'artist_profile_id' => 'Este perfil artístico já se candidatou a esta oportunidade.',
            ]);
        }

        $application = Application::query()->create([
            'opportunity_id' => $opportunity->id,
            'artist_profile_id' => $artist->id,
            'submitted_by_user_id' => $request->user()->id,
            'message' => $data['message'] ?? null,
            'status' => ApplicationStatus::Submitted,
        ]);

        $opportunity->loadMissing('venueProfile');
        $notifier->notifyMany(
            $notifier->venueManagerIds($opportunity->venueProfile),
            'application.submitted',
            'application:'.$application->id,
            'Nova candidatura',
            $artist->publicName().' candidatou-se a “'.$opportunity->title.'”.',
            ['url' => route('opportunities.show', $opportunity), 'application_id' => $application->id],
        );

        return redirect()
            ->route('opportunities.show', $opportunity)
            ->with('status', 'application-submitted');
    }

    public function updateStatus(Request $request, Opportunity $opportunity, Application $application, InAppNotifier $notifier): RedirectResponse
    {
        abort_unless($application->opportunity_id === $opportunity->id, 404);
        $this->authorize('updateStatus', $application);

        $data = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'venue_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $next = ApplicationStatus::from($data['status']);

        if ($next === $application->status) {
            $application->update([
                'venue_notes' => $data['venue_notes'] ?? $application->venue_notes,
            ]);

            return back()->with('status', 'application-updated');
        }

        if (! $application->canTransitionToByVenue($next)) {
            return back()->withErrors(['status' => 'Transição de status da candidatura não permitida.']);
        }

        if ($next === ApplicationStatus::Accepted) {
            $accepted = $opportunity->applications()
                ->where('status', ApplicationStatus::Accepted)
                ->count();

            if ($accepted >= max(1, (int) $opportunity->slots_count)) {
                return back()->withErrors(['status' => 'Todas as vagas já foram preenchidas.']);
            }
        }

        $application->update([
            'status' => $next,
            'venue_notes' => $data['venue_notes'] ?? $application->venue_notes,
            'reviewed_at' => now(),
        ]);

        $opportunity->refresh()->syncFilledIfSlotsComplete();
        $application->loadMissing('artistProfile');

        if (in_array($next, [ApplicationStatus::Accepted, ApplicationStatus::Rejected], true)) {
            $label = $next === ApplicationStatus::Accepted ? 'aceita' : 'recusada';
            $notifier->notifyMany(
                $notifier->artistManagerIds($application->artistProfile),
                'application.'.$next->value,
                'application:'.$application->id.':'.$next->value,
                'Candidatura '.$label,
                'Sua candidatura a “'.$opportunity->title.'” foi '.$label.'.',
                ['url' => route('opportunities.show', $opportunity), 'application_id' => $application->id],
            );
        }

        if ($next === ApplicationStatus::Accepted) {
            $conversation = Conversation::openForApplication($application->fresh());

            return redirect()
                ->route('conversations.show', $conversation)
                ->with('status', 'application-accepted-negotiation-opened');
        }

        return back()->with('status', 'application-status-updated');
    }

    public function withdraw(Request $request, Opportunity $opportunity, Application $application, InAppNotifier $notifier): RedirectResponse
    {
        abort_unless($application->opportunity_id === $opportunity->id, 404);
        $this->authorize('withdraw', $application);

        $application->update([
            'status' => ApplicationStatus::Withdrawn,
            'reviewed_at' => now(),
        ]);

        $opportunity->loadMissing('venueProfile');
        $application->loadMissing('artistProfile');
        $notifier->notifyMany(
            $notifier->venueManagerIds($opportunity->venueProfile),
            'application.withdrawn',
            'application:'.$application->id.':withdrawn',
            'Candidatura retirada',
            $application->artistProfile->publicName().' retirou a candidatura de “'.$opportunity->title.'”.',
            ['url' => route('opportunities.show', $opportunity), 'application_id' => $application->id],
        );

        return back()->with('status', 'application-withdrawn');
    }
}
