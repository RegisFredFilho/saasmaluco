<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function senderPhotoUrl(): ?string
    {
        $conversation = $this->conversation;

        if ($conversation?->artistProfile && $this->representsArtist($conversation->artistProfile)) {
            return $conversation->artistProfile->photoUrl();
        }

        if ($conversation?->venueProfile && $this->representsVenue($conversation->venueProfile)) {
            return $conversation->venueProfile->photoUrl();
        }

        return $this->user?->avatarUrl();
    }

    public function senderLabel(): string
    {
        $conversation = $this->conversation;

        if ($conversation?->artistProfile && $this->representsArtist($conversation->artistProfile)) {
            return $conversation->artistProfile->publicName();
        }

        if ($conversation?->venueProfile && $this->representsVenue($conversation->venueProfile)) {
            return $conversation->venueProfile->display_name;
        }

        return $this->user?->name ?? 'Participante';
    }

    private function representsArtist(ArtistProfile $artist): bool
    {
        if ((int) $artist->owner_user_id === (int) $this->user_id) {
            return true;
        }

        if ($artist->relationLoaded('members')) {
            return $artist->members->contains(fn (ArtistMember $member) => (int) $member->user_id === (int) $this->user_id);
        }

        return $artist->members()->where('user_id', $this->user_id)->exists();
    }

    private function representsVenue(VenueProfile $venue): bool
    {
        if ((int) $venue->owner_user_id === (int) $this->user_id) {
            return true;
        }

        if ($venue->relationLoaded('managers')) {
            return $venue->managers->contains(fn (VenueManager $manager) => (int) $manager->user_id === (int) $this->user_id);
        }

        return $venue->managers()->where('user_id', $this->user_id)->exists();
    }
}
