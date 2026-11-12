<?php

namespace App\Services\Semaninha;

use App\Models\SpotifyConnection;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SpotifyAuthClient
{
    public function authorizationUrl(User $user): string
    {
        $state = Str::random(40);

        session([
            'spotify_oauth_state' => $state,
            'spotify_oauth_user_id' => $user->id,
        ]);

        return 'https://accounts.spotify.com/authorize?'.http_build_query([
            'client_id' => config('services.spotify.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('services.spotify.redirect'),
            'scope' => implode(' ', config('services.spotify.scopes', [])),
            'state' => $state,
            'show_dialog' => 'true',
        ]);
    }

    public function handleCallback(User $user, string $code, ?string $state): SpotifyConnection
    {
        if (
            ! $state
            || $state !== session('spotify_oauth_state')
            || (int) session('spotify_oauth_user_id') !== (int) $user->id
        ) {
            throw new RuntimeException('State OAuth inválido.');
        }

        session()->forget(['spotify_oauth_state', 'spotify_oauth_user_id']);

        $response = Http::asForm()
            ->withBasicAuth(
                (string) config('services.spotify.client_id'),
                (string) config('services.spotify.client_secret'),
            )
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('services.spotify.redirect'),
            ]);

        if (! $response->successful()) {
            $detail = $response->json('error_description')
                ?? $response->json('error')
                ?? $response->body();

            throw new RuntimeException('Falha ao trocar o código Spotify: '.$detail);
        }

        return $this->persistTokens($user, $response->json());
    }

    public function connected(User $user): bool
    {
        return $user->spotifyConnection()->exists();
    }

    public function disconnect(User $user): void
    {
        $user->spotifyConnection()->delete();
    }

    public function accessToken(User $user): string
    {
        $connection = $user->spotifyConnection;

        if (! $connection) {
            throw new RuntimeException('Spotify não conectado.');
        }

        if ($connection->isExpired()) {
            $connection = $this->refreshTokens($user, $connection);
        }

        $token = $connection->access_token;

        if (! $token) {
            throw new RuntimeException('Spotify não conectado.');
        }

        return $token;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function persistTokens(User $user, array $payload, ?string $spotifyUserId = null): SpotifyConnection
    {
        $existing = $user->spotifyConnection;
        $refresh = $payload['refresh_token'] ?? $existing?->refresh_token;

        return SpotifyConnection::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'spotify_user_id' => $spotifyUserId ?? $existing?->spotify_user_id,
                'access_token' => $payload['access_token'] ?? null,
                'refresh_token' => $refresh,
                'expires_at' => now()->addSeconds((int) ($payload['expires_in'] ?? 3600)),
            ],
        );
    }

    private function refreshTokens(User $user, SpotifyConnection $connection): SpotifyConnection
    {
        if (! $connection->refresh_token) {
            $this->disconnect($user);
            throw new RuntimeException('Sessão Spotify expirada. Conecte novamente.');
        }

        $response = Http::asForm()
            ->withBasicAuth(
                (string) config('services.spotify.client_id'),
                (string) config('services.spotify.client_secret'),
            )
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $connection->refresh_token,
            ]);

        if (! $response->successful()) {
            $this->disconnect($user);
            throw new RuntimeException('Não foi possível renovar o token Spotify.');
        }

        $payload = $response->json();
        $payload['refresh_token'] = $payload['refresh_token'] ?? $connection->refresh_token;

        return $this->persistTokens($user, $payload, $connection->spotify_user_id);
    }
}
