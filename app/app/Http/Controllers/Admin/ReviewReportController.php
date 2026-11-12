<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Services\AdminAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewReportController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'open');

        $reports = ReviewReport::query()
            ->with(['review.author', 'reporter', 'resolvedBy'])
            ->when($filter === 'open', fn ($q) => $q->whereNull('resolved_at'))
            ->when($filter === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', compact('reports', 'filter'));
    }

    public function hide(Request $request, ReviewReport $report, AdminAuditor $auditor): RedirectResponse
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
                    'via' => 'report',
                    'report_id' => $report->id,
                ]);
            }

            $this->resolveOpenReports($review, $request->user()->id, 'hidden', $data['hidden_reason']);
        });

        return back()->with('status', 'report-hidden');
    }

    public function dismiss(Request $request, ReviewReport $report, AdminAuditor $auditor): RedirectResponse
    {
        $data = $request->validate([
            'resolution_note' => ['required', 'string', 'max:500'],
        ]);

        if ($report->resolved_at !== null) {
            return back()->with('status', 'report-already-resolved');
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
        ]);

        return back()->with('status', 'report-dismissed');
    }

    public function unhide(Request $request, Review $review, AdminAuditor $auditor): RedirectResponse
    {
        if (! $review->is_hidden) {
            return back()->with('status', 'review-already-visible');
        }

        $review->update([
            'is_hidden' => false,
            'hidden_reason' => null,
            'hidden_by_user_id' => null,
            'hidden_at' => null,
        ]);

        $auditor->log($request->user(), 'review.unhidden', $review);

        return back()->with('status', 'review-unhidden');
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
