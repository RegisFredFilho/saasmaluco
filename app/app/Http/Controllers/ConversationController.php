<?php

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Models\ArtistProfile;
use App\Models\Conversation;
use App\Models\VenueProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Conversation::class);

        $user = $request->user();

        $venueIds = VenueProfile::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('managers', fn ($q) => $q->where('user_id', $user->id));
            })
            ->pluck('id');

        $artistIds = ArtistProfile::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id));
            })
            ->get()
            ->filter(fn (ArtistProfile $artist) => $user->can('update', $artist))
            ->pluck('id');

        $conversations = Conversation::query()
            ->where(function ($query) use ($venueIds, $artistIds) {
                $query->whereIn('venue_profile_id', $venueIds)
                    ->orWhereIn('artist_profile_id', $artistIds);
            })
            ->with(['opportunity', 'venueProfile', 'artistProfile', 'application'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('conversations.index', compact('conversations'));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $conversation->load(['opportunity', 'venueProfile', 'artistProfile', 'application']);

        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at')
            ->paginate(30, ['*'], 'messages_page');

        $proposals = $conversation->proposals()
            ->with(['createdBy', 'booking'])
            ->orderByDesc('version')
            ->get();

        $hasAccepted = $proposals->contains(
            fn ($p) => $p->status === ProposalStatus::Accepted
        );

        return view('conversations.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'proposals' => $proposals,
            'canPropose' => $request->user()->can('propose', $conversation) && ! $hasAccepted,
            'hasAcceptedProposal' => $hasAccepted,
        ]);
    }
}
