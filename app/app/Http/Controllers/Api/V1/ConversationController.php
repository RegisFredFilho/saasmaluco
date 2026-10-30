<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ProposalResource;
use App\Models\ArtistProfile;
use App\Models\Conversation;
use App\Models\VenueProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
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
            ->with(['opportunity', 'venueProfile', 'artistProfile'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return ConversationResource::collection($conversations);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->load(['opportunity', 'venueProfile', 'artistProfile', 'application']);

        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at')
            ->paginate(30);

        $proposals = $conversation->proposals()
            ->with('booking')
            ->orderByDesc('version')
            ->get();

        $hasAccepted = $proposals->contains(
            fn ($p) => $p->status === ProposalStatus::Accepted
        );

        $messagesPayload = MessageResource::collection($messages)->response()->getData(true);
        $proposalsPayload = ProposalResource::collection($proposals)->resolve($request);

        return response()->json([
            'data' => (new ConversationResource($conversation))->resolve($request),
            'messages' => $messagesPayload,
            'proposals' => $proposalsPayload,
            'meta' => [
                'can_propose' => $request->user()->can('propose', $conversation) && ! $hasAccepted,
                'has_accepted_proposal' => $hasAccepted,
            ],
        ]);
    }
}
