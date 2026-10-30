<?php

namespace App\Http\Controllers;

use App\Enums\ArtistInviteStatus;
use App\Enums\ArtistMemberRole;
use App\Models\ArtistMember;
use App\Models\ArtistMemberInvite;
use App\Models\ArtistProfile;
use App\Models\User;
use App\Services\InAppNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArtistMemberInviteController extends Controller
{
    public function index(Request $request): View
    {
        $invites = ArtistMemberInvite::query()
            ->pending()
            ->where('invited_user_id', $request->user()->id)
            ->with(['artistProfile', 'invitedBy'])
            ->latest()
            ->paginate(20);

        return view('invites.index', compact('invites'));
    }

    public function store(Request $request, ArtistProfile $artistProfile, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('manageMembers', $artistProfile);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in([ArtistMemberRole::Admin->value, ArtistMemberRole::Member->value])],
        ]);

        $invitee = User::query()
            ->where('email', strtolower(trim($data['email'])))
            ->first();

        if (! $invitee || ! $invitee->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Não há conta ativa com este e-mail.'],
            ]);
        }

        if ((int) $invitee->id === (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'email' => ['Você não pode convidar a si mesmo.'],
            ]);
        }

        if ((int) $invitee->id === (int) $artistProfile->owner_user_id) {
            throw ValidationException::withMessages([
                'email' => ['O proprietário do perfil já é membro.'],
            ]);
        }

        if ($artistProfile->members()->where('user_id', $invitee->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Este usuário já é integrante do perfil.'],
            ]);
        }

        $pending = ArtistMemberInvite::query()
            ->pending()
            ->where('artist_profile_id', $artistProfile->id)
            ->where('invited_user_id', $invitee->id)
            ->exists();

        if ($pending) {
            throw ValidationException::withMessages([
                'email' => ['Já existe um convite pendente para este e-mail.'],
            ]);
        }

        $invite = ArtistMemberInvite::query()->create([
            'artist_profile_id' => $artistProfile->id,
            'invited_user_id' => $invitee->id,
            'invited_by_user_id' => $request->user()->id,
            'role' => ArtistMemberRole::from($data['role']),
            'status' => ArtistInviteStatus::Pending,
        ]);

        $notifier->notify(
            $invitee,
            'artist_invite.sent',
            'artist_invite:'.$invite->id,
            'Convite para banda',
            $request->user()->name.' convidou você para “'.$artistProfile->publicName().'” como '.$invite->role->value.'.',
            ['url' => route('invites.index'), 'invite_id' => $invite->id],
        );

        return back()->with('status', 'member-invite-sent');
    }

    public function accept(Request $request, ArtistMemberInvite $invite, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('accept', $invite);

        $invite->loadMissing('artistProfile');

        if ($invite->artistProfile->members()->where('user_id', $request->user()->id)->exists()) {
            $invite->update([
                'status' => ArtistInviteStatus::Accepted,
                'responded_at' => now(),
            ]);

            return redirect()->route('invites.index')->with('status', 'already-member');
        }

        ArtistMember::query()->firstOrCreate(
            [
                'artist_profile_id' => $invite->artist_profile_id,
                'user_id' => $request->user()->id,
            ],
            ['role' => $invite->role],
        );

        $invite->update([
            'status' => ArtistInviteStatus::Accepted,
            'responded_at' => now(),
        ]);

        $notifier->notifyMany(
            $notifier->artistManagerIds($invite->artistProfile),
            'artist_invite.accepted',
            'artist_invite:'.$invite->id.':accepted',
            'Convite aceito',
            $request->user()->name.' entrou em “'.$invite->artistProfile->publicName().'”.',
            ['url' => route('artist-profiles.edit', $invite->artistProfile), 'invite_id' => $invite->id],
        );

        return redirect()
            ->route('artist-profiles.edit', $invite->artistProfile)
            ->with('status', 'member-invite-accepted');
    }

    public function decline(Request $request, ArtistMemberInvite $invite, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('decline', $invite);

        $invite->loadMissing('artistProfile');
        $invite->update([
            'status' => ArtistInviteStatus::Declined,
            'responded_at' => now(),
        ]);

        $notifier->notify(
            $invite->invited_by_user_id,
            'artist_invite.declined',
            'artist_invite:'.$invite->id.':declined',
            'Convite recusado',
            $request->user()->name.' recusou o convite para “'.$invite->artistProfile->publicName().'”.',
            ['url' => route('artist-profiles.edit', $invite->artistProfile), 'invite_id' => $invite->id],
        );

        return redirect()->route('invites.index')->with('status', 'member-invite-declined');
    }

    public function cancel(Request $request, ArtistMemberInvite $invite): RedirectResponse
    {
        $this->authorize('cancel', $invite);

        $invite->update([
            'status' => ArtistInviteStatus::Cancelled,
            'responded_at' => now(),
        ]);

        return back()->with('status', 'member-invite-cancelled');
    }

    public function destroyMember(Request $request, ArtistProfile $artistProfile, ArtistMember $member): RedirectResponse
    {
        $this->authorize('manageMembers', $artistProfile);
        abort_unless($member->artist_profile_id === $artistProfile->id, 404);

        if ($member->role === ArtistMemberRole::Owner
            || (int) $member->user_id === (int) $artistProfile->owner_user_id) {
            return back()->withErrors(['member' => 'O proprietário não pode ser removido.']);
        }

        if ((int) $member->user_id === (int) $request->user()->id
            && $artistProfile->owner_user_id !== $request->user()->id) {
            // admin removing self is ok
        }

        $member->delete();

        return back()->with('status', 'member-removed');
    }
}
