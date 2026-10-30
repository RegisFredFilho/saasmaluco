<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppNotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(30);

        return AppNotificationResource::collection($notifications);
    }

    public function markRead(Request $request, AppNotification $notification): AppNotificationResource|JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        $notification->markRead();

        return AppNotificationResource::make($notification->fresh());
    }

    public function markAllRead(Request $request): JsonResponse
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notificações marcadas como lidas.']);
    }
}
