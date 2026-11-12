<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
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
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function suspend(Request $request, User $user, AdminAuditor $auditor): RedirectResponse
    {
        if ($user->isPlatformAdmin()) {
            return back()->withErrors(['user' => 'Não é permitido suspender um admin da plataforma por esta tela.']);
        }

        if (! $user->isActive()) {
            return back()->with('status', 'user-already-suspended');
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
        ]);

        return back()->with('status', 'user-suspended');
    }

    public function reactivate(Request $request, User $user, AdminAuditor $auditor): RedirectResponse
    {
        if ($user->isActive()) {
            return back()->with('status', 'user-already-active');
        }

        $user->forceFill([
            'deactivated_at' => null,
            'deactivation_reason' => null,
        ])->save();

        $auditor->log($request->user(), 'user.reactivated', $user);

        return back()->with('status', 'user-reactivated');
    }
}
