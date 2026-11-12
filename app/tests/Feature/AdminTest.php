<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ArtistKind;
use App\Enums\BookingStatus;
use App\Enums\OpportunityStatus;
use App\Enums\ProposalStatus;
use App\Models\AdminAuditLog;
use App\Models\Application;
use App\Models\ArtistProfile;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Opportunity;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use App\Models\VenueProfile;
use App\Services\CreateBookingFromProposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_area(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin da plataforma');
    }

    public function test_admin_can_suspend_user_with_reason_and_audit(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.suspend', $target), [
            'deactivation_reason' => 'Abuso de plataforma',
        ])->assertRedirect();

        $target->refresh();
        $this->assertFalse($target->isActive());
        $this->assertSame('Abuso de plataforma', $target->deactivation_reason);

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'action' => 'user.suspended',
            'subject_type' => $target->getMorphClass(),
            'subject_id' => $target->id,
        ]);

        $this->post(route('logout'));
        $this->post(route('login'), [
            'email' => $target->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_suspend_another_admin(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $otherAdmin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)->post(route('admin.users.suspend', $otherAdmin), [
            'deactivation_reason' => 'Tentativa inválida',
        ])->assertSessionHasErrors('user');

        $this->assertTrue($otherAdmin->fresh()->isActive());
    }

    public function test_admin_can_resolve_report_by_hiding_review(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        [$review, $reporter] = $this->reviewWithReport();

        $report = ReviewReport::query()->create([
            'review_id' => $review->id,
            'reporter_user_id' => $reporter->id,
            'reason' => 'Conteúdo ofensivo',
        ]);

        $this->actingAs($admin)->post(route('admin.reports.hide', $report), [
            'hidden_reason' => 'Viola diretrizes',
        ])->assertRedirect();

        $this->assertTrue($review->fresh()->is_hidden);
        $this->assertNotNull($report->fresh()->resolved_at);
        $this->assertSame('hidden', $report->fresh()->resolution);

        $this->assertTrue(AdminAuditLog::query()
            ->where('action', 'review.hidden')
            ->where('actor_user_id', $admin->id)
            ->exists());
    }

    public function test_admin_can_dismiss_report_and_unhide_review(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        [$review, $reporter] = $this->reviewWithReport();

        $report = ReviewReport::query()->create([
            'review_id' => $review->id,
            'reporter_user_id' => $reporter->id,
            'reason' => 'Discordância sem abuso',
        ]);

        $this->actingAs($admin)->post(route('admin.reports.dismiss', $report), [
            'resolution_note' => 'Sem violação clara',
        ])->assertRedirect();

        $this->assertSame('dismissed', $report->fresh()->resolution);
        $this->assertFalse($review->fresh()->is_hidden);

        $review->update([
            'is_hidden' => true,
            'hidden_reason' => 'teste',
            'hidden_by_user_id' => $admin->id,
            'hidden_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.reviews.unhide', $review))
            ->assertRedirect();

        $this->assertFalse($review->fresh()->is_hidden);
        $this->assertTrue(AdminAuditLog::query()->where('action', 'review.unhidden')->exists());
    }

    /**
     * @return array{0: Review, 1: User}
     */
    private function reviewWithReport(): array
    {
        $venueOwner = User::factory()->create();
        $artistOwner = User::factory()->create();
        $reporter = User::factory()->create();

        $venue = VenueProfile::create([
            'owner_user_id' => $venueOwner->id,
            'display_name' => 'Bar Admin',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Show admin',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->subDays(3)->toDateString(),
            'starts_at' => '20:00:00',
            'ends_at' => '22:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Artista Admin',
            'kind' => ArtistKind::Band,
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $application = Application::query()->create([
            'opportunity_id' => $opportunity->id,
            'artist_profile_id' => $artist->id,
            'submitted_by_user_id' => $artistOwner->id,
            'status' => ApplicationStatus::Accepted,
        ]);

        $conversation = Conversation::openForApplication($application);

        $proposal = Proposal::query()->create([
            'conversation_id' => $conversation->id,
            'created_by_user_id' => $venueOwner->id,
            'amount' => 900,
            'currency' => 'BRL',
            'status' => ProposalStatus::Accepted,
            'version' => 1,
            'responded_at' => now(),
        ]);

        /** @var Booking $booking */
        $booking = app(CreateBookingFromProposal::class)->handle($proposal);
        $booking->update([
            'status' => BookingStatus::Completed,
            'artist_completed_at' => now()->subDay(),
            'venue_completed_at' => now()->subDay(),
            'completed_at' => now()->subDay(),
        ]);

        $review = Review::query()->create([
            'booking_id' => $booking->id,
            'author_user_id' => $artistOwner->id,
            'author_side' => 'artist',
            'rateable_type' => $venue->getMorphClass(),
            'rateable_id' => $venue->id,
            'rating' => 2,
            'comment' => 'Comentário alvo',
        ]);

        return [$review, $reporter];
    }
}
