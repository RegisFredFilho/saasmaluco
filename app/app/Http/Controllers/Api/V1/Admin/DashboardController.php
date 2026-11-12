<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\BookingStatus;
use App\Enums\OpportunityStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Booking;
use App\Models\Opportunity;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'stats' => [
                    'users' => User::query()->count(),
                    'active_users' => User::query()->whereNull('deactivated_at')->count(),
                    'suspended_users' => User::query()->whereNotNull('deactivated_at')->count(),
                    'open_reports' => ReviewReport::query()->whereNull('resolved_at')->count(),
                    'hidden_reviews' => Review::query()->where('is_hidden', true)->count(),
                    'open_opportunities' => Opportunity::query()->where('status', OpportunityStatus::Open)->count(),
                    'confirmed_bookings' => Booking::query()->where('status', BookingStatus::Confirmed)->count(),
                ],
                'recent_audits' => AdminAuditLog::query()
                    ->with('actor:id,name,email')
                    ->latest('created_at')
                    ->limit(15)
                    ->get()
                    ->map(fn (AdminAuditLog $log) => [
                        'id' => $log->id,
                        'action' => $log->action,
                        'actor' => $log->actor?->only(['id', 'name', 'email']),
                        'subject_type' => $log->subject_type,
                        'subject_id' => $log->subject_id,
                        'payload' => $log->payload,
                        'created_at' => $log->created_at,
                    ]),
            ],
        ]);
    }
}
