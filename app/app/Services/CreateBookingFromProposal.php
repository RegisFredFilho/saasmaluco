<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Proposal;
use RuntimeException;

class CreateBookingFromProposal
{
    public function handle(Proposal $proposal): Booking
    {
        $proposal->loadMissing(['conversation.opportunity', 'conversation.application']);

        $conversation = $proposal->conversation;
        $opportunity = $conversation->opportunity;
        [$startsAt, $endsAt] = Booking::windowFromOpportunity($opportunity);

        if (Booking::hasConflict($conversation->artist_profile_id, $startsAt, $endsAt)) {
            throw new RuntimeException('Conflito de agenda: o artista já tem booking confirmado neste intervalo.');
        }

        return Booking::query()->create([
            'proposal_id' => $proposal->id,
            'conversation_id' => $conversation->id,
            'application_id' => $conversation->application_id,
            'opportunity_id' => $conversation->opportunity_id,
            'artist_profile_id' => $conversation->artist_profile_id,
            'venue_profile_id' => $conversation->venue_profile_id,
            'amount' => $proposal->amount,
            'currency' => $proposal->currency ?: 'BRL',
            'conditions' => $proposal->conditions,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'timezone' => 'America/Sao_Paulo',
            'status' => BookingStatus::Confirmed,
        ]);
    }
}
