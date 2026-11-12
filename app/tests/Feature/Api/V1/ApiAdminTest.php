<?php

namespace Tests\Feature\Api\V1;

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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_api(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/admin')->assertForbidden();
        $this->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_admin_can_view_dashboard_and_suspend_user(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $target = User::factory()->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin')
            ->assertOk()
            ->assertJsonStructure(['data' => ['stats', 'recent_audits']]);

        $this->postJson("/api/v1/admin/users/{$target->id}/suspend", [
            'deactivation_reason' => 'Abuso via API',
        ])->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($target->fresh()->isActive());
        $this->assertTrue(AdminAuditLog::query()
            ->where('action', 'user.suspended')
            ->where('actor_user_id', $admin->id)
            ->exists());
    }

    public function test_admin_can_hide_report_via_api(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        [$review, $reporter] = $this->reviewWithReport();

        $report = ReviewReport::query()->create([
            'review_id' => $review->id,
            'reporter_user_id' => $reporter->id,
            'reason' => 'Ofensivo',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/reports?filter=open')
            ->assertOk()
            ->assertJsonFragment(['id' => $report->id]);

        $this->postJson("/api/v1/admin/reports/{$report->id}/hide", [
            'hidden_reason' => 'Viola diretrizes',
        ])->assertOk()
            ->assertJsonPath('message', 'report-hidden');

        $this->assertTrue($review->fresh()->is_hidden);
        $this->assertNotNull($report->fresh()->resolved_at);
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
            'display_name' => 'Bar API Admin',
            'is_public' => true,
            'city' => 'Curitiba',
            'state' => 'PR',
        ]);

        $opportunity = Opportunity::query()->create([
            'venue_profile_id' => $venue->id,
            'created_by_user_id' => $venueOwner->id,
            'title' => 'Show API admin',
            'status' => OpportunityStatus::Open,
            'slots_count' => 1,
            'event_date' => now()->subDays(3)->toDateString(),
            'starts_at' => '20:00:00',
            'ends_at' => '22:00:00',
        ]);

        $artist = ArtistProfile::create([
            'owner_user_id' => $artistOwner->id,
            'display_name' => 'Artista API Admin',
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
            'rating' => 1,
            'comment' => 'Comentário abusivo',
        ]);

        return [$review, $reporter];
    }
}
