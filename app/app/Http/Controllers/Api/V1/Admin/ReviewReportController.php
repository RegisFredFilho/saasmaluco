<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Services\AdminAuditor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'open');

        $reports = ReviewReport::query()
            ->with(['review.author:id,name', 'reporter:id,name,email', 'resolvedBy:id,name'])
            ->when($filter === 'open', fn ($q) => $q->whereNull('resolved_at'))
            ->when($filter === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'))
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $reports->getCollection()->map(fn (ReviewReport $report) => $this->serializeReport($report)),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'filter' => $filter,
            ],
        ]);
    }

    public function hide(Request $request, ReviewReport $report, AdminAuditor $auditor): JsonResponse
    {
        $data = $request->validate([
            'hidden_reason' => ['required', 'string', 'max:500'],
        ]);

        $review = $report->review()->firstOrFail();

        DB::transaction(function () use ($request, $report, $review, $data, $auditor) {
            if (! $review->is_hidden) {
                $review->update([
                    'is_hidden' => true,
                    'hidden_reason' => $data['hidden_reason'],
                    'hidden_by_user_id' => $request->user()->id,
                    'hidden_at' => now(),
                ]);

                $auditor->log($request->user(), 'review.hidden', $review, [
                    'reason' => $data['hidden_reason'],
                    'via' => 'api-report',
                    'report_id' => $report->id,
                ]);
            }

            $this->resolveOpenReports($review, $request->user()->id, 'hidden', $data['hidden_reason']);
        });

        return response()->json([
            'data' => $this->serializeReport($report->fresh()->load(['review.author', 'reporter', 'resolvedBy'])),
            'message' => 'report-hidden',
        ]);
    }

    public function dismiss(Request $request, ReviewReport $report, AdminAuditor $auditor): JsonResponse
    {
        $data = $request->validate([
            'resolution_note' => ['required', 'string', 'max:500'],
        ]);

        if ($report->resolved_at !== null) {
            return response()->json([
                'data' => $this->serializeReport($report->load(['review.author', 'reporter', 'resolvedBy'])),
                'message' => 'report-already-resolved',
            ]);
        }

        $report->update([
            'resolved_at' => now(),
            'resolved_by_user_id' => $request->user()->id,
            'resolution' => 'dismissed',
            'resolution_note' => $data['resolution_note'],
        ]);

        $auditor->log($request->user(), 'review_report.dismissed', $report, [
            'note' => $data['resolution_note'],
            'review_id' => $report->review_id,
            'via' => 'api',
        ]);

        return response()->json([
            'data' => $this->serializeReport($report->fresh()->load(['review.author', 'reporter', 'resolvedBy'])),
            'message' => 'report-dismissed',
        ]);
    }

    public function unhide(Request $request, Review $review, AdminAuditor $auditor): JsonResponse
    {
        if (! $review->is_hidden) {
            return response()->json([
                'data' => [
                    'id' => $review->id,
                    'is_hidden' => false,
                ],
                'message' => 'review-already-visible',
            ]);
        }

        $review->update([
            'is_hidden' => false,
            'hidden_reason' => null,
            'hidden_by_user_id' => null,
            'hidden_at' => null,
        ]);

        $auditor->log($request->user(), 'review.unhidden', $review, [
            'via' => 'api',
        ]);

        return response()->json([
            'data' => [
                'id' => $review->id,
                'is_hidden' => false,
            ],
            'message' => 'review-unhidden',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeReport(ReviewReport $report): array
    {
        return [
            'id' => $report->id,
            'reason' => $report->reason,
            'resolution' => $report->resolution,
            'resolution_note' => $report->resolution_note,
            'resolved_at' => $report->resolved_at,
            'created_at' => $report->created_at,
            'reporter' => $report->reporter?->only(['id', 'name', 'email']),
            'resolved_by' => $report->resolvedBy?->only(['id', 'name']),
            'review' => $report->review ? [
                'id' => $report->review->id,
                'rating' => $report->review->rating,
                'comment' => $report->review->comment,
                'is_hidden' => $report->review->is_hidden,
                'hidden_reason' => $report->review->hidden_reason,
                'author' => $report->review->author?->only(['id', 'name']),
            ] : null,
        ];
    }

    private function resolveOpenReports(Review $review, int $adminId, string $resolution, string $note): void
    {
        ReviewReport::query()
            ->where('review_id', $review->id)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => now(),
                'resolved_by_user_id' => $adminId,
                'resolution' => $resolution,
                'resolution_note' => $note,
            ]);
    }
}
