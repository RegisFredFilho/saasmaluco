<?php

namespace App\Http\Controllers\Semaninha;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Semaninha\SpotifyAuthClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SpotifyAuthController extends Controller
{
    public function connect(SpotifyAuthClient $auth): RedirectResponse
    {
        if (! filled(config('services.spotify.client_id')) || ! filled(config('services.spotify.client_secret'))) {
            return redirect()
                ->route('semaninha.index')
                ->with('error', 'Configure SPOTIFY_CLIENT_ID e SPOTIFY_CLIENT_SECRET no .env.');
        }

        return redirect()->away($auth->authorizationUrl($this->user()));
    }

    public function callback(Request $request, SpotifyAuthClient $auth): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('semaninha.index')
                ->with('error', 'Autorização Spotify cancelada.');
        }

        try {
            $auth->handleCallback(
                $this->user(),
                (string) $request->query('code'),
                $request->query('state'),
            );
        } catch (RuntimeException $e) {
            return redirect()->route('semaninha.index')->with('error', $e->getMessage());
        }

        return redirect()->route('semaninha.studio')->with('status', 'Spotify conectado.');
    }

    public function disconnect(SpotifyAuthClient $auth): RedirectResponse
    {
        $auth->disconnect($this->user());

        return redirect()->route('semaninha.index')->with('status', 'Spotify desconectado.');
    }

    private function user(): User
    {
        return auth()->user();
    }
}
