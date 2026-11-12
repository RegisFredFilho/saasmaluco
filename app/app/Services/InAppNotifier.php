<?php

namespace App\Services;

use App\Enums\ArtistMemberRole;
use App\Mail\AppNotificationMail;
use App\Models\AppNotification;
use App\Models\ArtistProfile;
use App\Models\User;
use App\Models\VenueProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InAppNotifier
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function notify(User|int $user, string $type, string $idempotencyKey, string $title, string $body, array $data = []): ?AppNotification
    {
        $userModel = $user instanceof User
            ? $user
            : User::query()->find($user);

        $userId = $userModel?->id ?? (int) $user;

        if ($userId <= 0) {
            return null;
        }

        try {
            $notification = AppNotification::query()->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'idempotency_key' => $idempotencyKey,
            ]);
        } catch (Throwable) {
            // Unique idempotency_key: reprocessamento seguro (sem reenviar e-mail)
            return AppNotification::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();
        }

        $this->maybeSendEmail($userModel, $notification);

        return $notification;
    }

    /**
     * @param  iterable<int|User>  $users
     * @param  array<string, mixed>  $data
     */
    public function notifyMany(iterable $users, string $type, string $keySuffix, string $title, string $body, array $data = []): void
    {
        $seen = [];

        foreach ($users as $user) {
            $userId = $user instanceof User ? $user->id : (int) $user;
            if ($userId <= 0 || isset($seen[$userId])) {
                continue;
            }
            $seen[$userId] = true;

            $this->notify(
                $user instanceof User ? $user : $userId,
                $type,
                $type.':'.$keySuffix.':user:'.$userId,
                $title,
                $body,
                $data,
            );
        }
    }

    /**
     * @return Collection<int, int>
     */
    public function venueManagerIds(VenueProfile $venue): Collection
    {
        $ids = collect([$venue->owner_user_id]);

        $managerIds = DB::table('venue_managers')
            ->where('venue_profile_id', $venue->id)
            ->pluck('user_id');

        return $ids->merge($managerIds)->unique()->values();
    }

    /**
     * @return Collection<int, int>
     */
    public function artistManagerIds(ArtistProfile $artist): Collection
    {
        $ids = collect([$artist->owner_user_id]);

        $memberIds = DB::table('artist_members')
            ->where('artist_profile_id', $artist->id)
            ->whereIn('role', [ArtistMemberRole::Owner->value, ArtistMemberRole::Admin->value])
            ->pluck('user_id');

        return $ids->merge($memberIds)->unique()->values();
    }

    private function maybeSendEmail(?User $user, AppNotification $notification): void
    {
        if (! $user || ! $user->isActive() || ! filled($user->email)) {
            return;
        }

        if (! (bool) data_get($user->preferences, 'notify_email', true)) {
            return;
        }

        Mail::to($user->email)->send(new AppNotificationMail($notification));
    }
}
