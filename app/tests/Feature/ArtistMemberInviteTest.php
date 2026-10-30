<?php

namespace Tests\Feature;

use App\Enums\ArtistInviteStatus;
use App\Enums\ArtistKind;
use App\Enums\ArtistMemberRole;
use App\Models\AppNotification;
use App\Models\ArtistMember;
use App\Models\ArtistMemberInvite;
use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistMemberInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_existing_user_and_invitee_can_accept(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'membro@example.com']);

        $band = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Convite',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $this->actingAs($owner)->post(route('artist-profiles.invites.store', $band), [
            'email' => 'membro@example.com',
            'role' => 'admin',
        ])->assertRedirect();

        $invite = ArtistMemberInvite::query()->first();
        $this->assertNotNull($invite);
        $this->assertSame(ArtistInviteStatus::Pending, $invite->status);
        $this->assertSame(ArtistMemberRole::Admin, $invite->role);

        $this->assertTrue(AppNotification::query()
            ->where('user_id', $invitee->id)
            ->where('type', 'artist_invite.sent')
            ->exists());

        $this->actingAs($invitee)
            ->get(route('invites.index'))
            ->assertOk()
            ->assertSee('Banda Convite');

        $this->actingAs($invitee)
            ->post(route('invites.accept', $invite))
            ->assertRedirect(route('artist-profiles.edit', $band));

        $this->assertTrue(ArtistMember::query()
            ->where('artist_profile_id', $band->id)
            ->where('user_id', $invitee->id)
            ->where('role', ArtistMemberRole::Admin->value)
            ->exists());

        $this->assertSame(ArtistInviteStatus::Accepted, $invite->fresh()->status);
    }

    public function test_stranger_cannot_accept_and_cannot_invite_without_manage(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create();
        $stranger = User::factory()->create();

        $band = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Fechada',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $invite = ArtistMemberInvite::query()->create([
            'artist_profile_id' => $band->id,
            'invited_user_id' => $invitee->id,
            'invited_by_user_id' => $owner->id,
            'role' => ArtistMemberRole::Member,
            'status' => ArtistInviteStatus::Pending,
        ]);

        $this->actingAs($stranger)
            ->post(route('invites.accept', $invite))
            ->assertForbidden();

        $this->actingAs($stranger)->post(route('artist-profiles.invites.store', $band), [
            'email' => $invitee->email,
            'role' => 'member',
        ])->assertForbidden();
    }

    public function test_invitee_can_decline_and_owner_can_cancel(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create();

        $band = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Decline',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $invite = ArtistMemberInvite::query()->create([
            'artist_profile_id' => $band->id,
            'invited_user_id' => $invitee->id,
            'invited_by_user_id' => $owner->id,
            'role' => ArtistMemberRole::Member,
            'status' => ArtistInviteStatus::Pending,
        ]);

        $this->actingAs($invitee)
            ->post(route('invites.decline', $invite))
            ->assertRedirect(route('invites.index'));

        $this->assertSame(ArtistInviteStatus::Declined, $invite->fresh()->status);

        $invite2 = ArtistMemberInvite::query()->create([
            'artist_profile_id' => $band->id,
            'invited_user_id' => $invitee->id,
            'invited_by_user_id' => $owner->id,
            'role' => ArtistMemberRole::Member,
            'status' => ArtistInviteStatus::Pending,
        ]);

        $this->actingAs($owner)
            ->delete(route('invites.cancel', $invite2))
            ->assertRedirect();

        $this->assertSame(ArtistInviteStatus::Cancelled, $invite2->fresh()->status);
    }

    public function test_cannot_invite_unknown_email_or_existing_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['email' => 'ja@example.com']);

        $band = ArtistProfile::create([
            'owner_user_id' => $owner->id,
            'display_name' => 'Banda Unique',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        ArtistMember::query()->create([
            'artist_profile_id' => $band->id,
            'user_id' => $member->id,
            'role' => ArtistMemberRole::Member,
        ]);

        $this->actingAs($owner)->post(route('artist-profiles.invites.store', $band), [
            'email' => 'naoexiste@example.com',
            'role' => 'member',
        ])->assertSessionHasErrors('email');

        $this->actingAs($owner)->post(route('artist-profiles.invites.store', $band), [
            'email' => 'ja@example.com',
            'role' => 'member',
        ])->assertSessionHasErrors('email');
    }
}
