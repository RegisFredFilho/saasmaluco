<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ProposalResource;
use App\Models\Conversation;
use App\Models\Proposal;
use App\Services\CreateBookingFromProposal;
use App\Services\InAppNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProposalController extends Controller
{
    public function store(Request $request, Conversation $conversation, InAppNotifier $notifier): JsonResponse
    {
        $this->authorize('propose', $conversation);

        if ($conversation->proposals()->where('status', ProposalStatus::Accepted)->exists()) {
            throw ValidationException::withMessages([
                'amount' => ['Já existe proposta aceita. Use a agenda/booking para o compromisso.'],
            ]);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'conditions' => ['nullable', 'string', 'max:5000'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $proposal = DB::transaction(function () use ($request, $conversation, $data) {
            $previousSent = $conversation->proposals()
                ->where('status', ProposalStatus::Sent)
                ->lockForUpdate()
                ->get();

            $version = ((int) $conversation->proposals()->max('version')) + 1;
            $supersedesId = $previousSent->sortByDesc('version')->first()?->id;

            $proposal = $conversation->proposals()->create([
                'created_by_user_id' => $request->user()->id,
                'amount' => $data['amount'],
                'currency' => 'BRL',
                'conditions' => $data['conditions'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'status' => ProposalStatus::Sent,
                'version' => max(1, $version),
                'supersedes_id' => $supersedesId,
            ]);

            foreach ($previousSent as $old) {
                $old->update([
                    'status' => ProposalStatus::Superseded,
                    'superseded_by_id' => $proposal->id,
                    'responded_at' => now(),
                ]);
            }

            $conversation->update(['last_message_at' => now()]);

            return $proposal;
        });

        $conversation->loadMissing(['venueProfile', 'artistProfile', 'opportunity']);
        $recipients = $notifier->venueManagerIds($conversation->venueProfile)
            ->merge($notifier->artistManagerIds($conversation->artistProfile))
            ->reject(fn ($id) => (int) $id === (int) $request->user()->id)
            ->values();

        $notifier->notifyMany(
            $recipients,
            'proposal.sent',
            'proposal:'.$proposal->id,
            'Nova proposta',
            'Nova proposta em “'.$conversation->opportunity->title.'” (R$ '.number_format((float) $proposal->amount, 2, ',', '.').').',
            ['url' => route('conversations.show', $conversation), 'proposal_id' => $proposal->id],
        );

        return ProposalResource::make($proposal->load('booking'))
            ->response()
            ->setStatusCode(201);
    }

    public function respond(Request $request, Conversation $conversation, Proposal $proposal, InAppNotifier $notifier): JsonResponse
    {
        abort_unless($proposal->conversation_id === $conversation->id, 404);
        $this->authorize('respond', $proposal);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'rejected'])],
        ]);

        if ($conversation->proposals()->where('status', ProposalStatus::Accepted)->exists()) {
            throw ValidationException::withMessages([
                'decision' => ['Já existe uma proposta aceita nesta negociação.'],
            ]);
        }

        $conversation->loadMissing(['venueProfile', 'artistProfile', 'opportunity']);

        if ($data['decision'] === 'rejected') {
            $proposal->update([
                'status' => ProposalStatus::Rejected,
                'responded_at' => now(),
            ]);
            $conversation->update(['last_message_at' => now()]);

            $notifier->notify(
                $proposal->created_by_user_id,
                'proposal.rejected',
                'proposal:'.$proposal->id.':rejected',
                'Proposta recusada',
                'Sua proposta em “'.$conversation->opportunity->title.'” foi recusada.',
                ['url' => route('conversations.show', $conversation), 'proposal_id' => $proposal->id],
            );

            return response()->json([
                'data' => (new ProposalResource($proposal->fresh()->load('booking')))->resolve($request),
            ]);
        }

        try {
            $booking = DB::transaction(function () use ($proposal, $conversation) {
                $proposal->update([
                    'status' => ProposalStatus::Accepted,
                    'responded_at' => now(),
                ]);

                $conversation->update(['last_message_at' => now()]);

                return app(CreateBookingFromProposal::class)->handle($proposal->fresh());
            });
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'decision' => [$e->getMessage()],
            ]);
        }

        $participants = $notifier->venueManagerIds($conversation->venueProfile)
            ->merge($notifier->artistManagerIds($conversation->artistProfile))
            ->values();

        $notifier->notifyMany(
            $participants,
            'booking.confirmed',
            'booking:'.$booking->id,
            'Booking confirmado',
            'Compromisso confirmado: “'.$conversation->opportunity->title.'”.',
            ['url' => route('bookings.show', $booking), 'booking_id' => $booking->id],
        );

        return response()->json([
            'data' => (new ProposalResource($proposal->fresh()->load('booking')))->resolve($request),
            'booking' => (new BookingResource($booking->load(['opportunity', 'artistProfile', 'venueProfile'])))->resolve($request),
        ]);
    }
}
