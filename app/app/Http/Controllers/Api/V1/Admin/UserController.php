<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AdminAuditor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

        return UserResource::collection($users);
    }

    public function suspend(Request $request, User $user, AdminAuditor $auditor): JsonResponse
    {
        if ($user->isPlatformAdmin()) {
            throw ValidationException::withMessages([
                'user' => ['Não é permitido suspender um admin da plataforma por esta API.'],
            ]);
        }

        if (! $user->isActive()) {
            return response()->json([
                'data' => (new UserResource($user))->resolve($request),
                'message' => 'user-already-suspended',
            ]);
        }

        $data = $request->validate([
            'deactivation_reason' => ['required', 'string', 'max:500'],
        ]);

        $user->forceFill([
            'deactivated_at' => now(),
            'deactivation_reason' => $data['deactivation_reason'],
        ])->save();

        $auditor->log($request->user(), 'user.suspended', $user, [
            'reason' => $data['deactivation_reason'],
            'via' => 'api',
        ]);

        return response()->json([
            'data' => (new UserResource($user->fresh()))->resolve($request),
            'message' => 'user-suspended',
        ]);
    }

    public function reactivate(Request $request, User $user, AdminAuditor $auditor): JsonResponse
    {
        if ($user->isActive()) {
            return response()->json([
                'data' => (new UserResource($user))->resolve($request),
                'message' => 'user-already-active',
            ]);
        }

        $user->forceFill([
            'deactivated_at' => null,
            'deactivation_reason' => null,
        ])->save();

        $auditor->log($request->user(), 'user.reactivated', $user, [
            'via' => 'api',
        ]);

        return response()->json([
            'data' => (new UserResource($user->fresh()))->resolve($request),
            'message' => 'user-reactivated',
        ]);
    }
}
