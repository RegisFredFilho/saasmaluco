<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $token = $user->currentAccessToken();
                if ($token) {
                    $token->delete();
                }

                return response()->json([
                    'message' => 'Esta conta está desativada.',
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Esta conta está desativada.']);
        }

        return $next($request);
    }
}
