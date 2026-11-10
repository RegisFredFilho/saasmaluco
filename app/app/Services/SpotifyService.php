<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SpotifyService
{
    public function authorizationUrl(): string
    {
        $state = Str::random(40);
        session(['spotify_oauth_state' => $state]);

        return 'https://accounts.spotify.com/authorize?'.http_build_query([
            'client_id' => config('services.spotify.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('services.spotify.redirect'),
            'scope' => implode(' ', config('services.spotify.scopes', [])),
            'state' => $state,
            'show_dialog' => 'true',
        ]);
    }

    public function handleCallback(string $code, ?string $state): void
    {
        if (! $state || $state !== session('spotify_oauth_state')) {
            throw new RuntimeException('State OAuth inválido.');
        }

        session()->forget('spotify_oauth_state');

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

        $this->storeTokens($response->json());
    }

    public function connected(): bool
    {
        return filled(session('spotify.access_token'));
    }

    public function disconnect(): void
    {
        session()->forget('spotify');
    }

    /**
     * @return list<array{title: string, image: string, url: string}>
     */
    public function topAlbumCovers(int $limit = 20): array
    {
        $tracks = $this->get('me/top/tracks', [
            'limit' => min($limit, 50),
            'time_range' => 'medium_term',
        ]);

        $covers = [];
        $seen = [];

        foreach ($tracks['items'] ?? [] as $track) {
            $album = $track['album'] ?? [];
            $image = $album['images'][0]['url'] ?? ($album['images'][1]['url'] ?? null);
            $albumId = $album['id'] ?? $image;

            if (! $image || isset($seen[$albumId])) {
                continue;
            }

            $seen[$albumId] = true;
            $covers[] = [
                'title' => (string) ($album['name'] ?? $track['name'] ?? 'Álbum'),
                'image' => $image,
                'url' => (string) ($album['external_urls']['spotify'] ?? $track['external_urls']['spotify'] ?? '#'),
            ];
        }

        if (count($covers) < 9) {
            $artists = $this->get('me/top/artists', [
                'limit' => 20,
                'time_range' => 'medium_term',
            ]);

            foreach ($artists['items'] ?? [] as $artist) {
                $image = $artist['images'][0]['url'] ?? ($artist['images'][1]['url'] ?? null);
                $artistId = $artist['id'] ?? $image;

                if (! $image || isset($seen[$artistId])) {
                    continue;
                }

                $seen[$artistId] = true;
                $covers[] = [
                    'title' => (string) ($artist['name'] ?? 'Artista'),
                    'image' => $image,
                    'url' => (string) ($artist['external_urls']['spotify'] ?? '#'),
                ];

                if (count($covers) >= $limit) {
                    break;
                }
            }
        }

        return array_slice($covers, 0, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        $token = $this->accessToken();

        $response = Http::withToken($token)
            ->get('https://api.spotify.com/v1/'.$path, $query);

        if ($response->status() === 401) {
            $this->refreshTokens();
            $response = Http::withToken($this->accessToken())
                ->get('https://api.spotify.com/v1/'.$path, $query);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Falha ao consultar a API do Spotify.');
        }

        return $response->json();
    }

    private function accessToken(): string
    {
        $expiresAt = (int) session('spotify.expires_at', 0);

        if ($expiresAt > 0 && now()->timestamp >= ($expiresAt - 60)) {
            $this->refreshTokens();
        }

        $token = session('spotify.access_token');

        if (! $token) {
            throw new RuntimeException('Spotify não conectado.');
        }

        return (string) $token;
    }

    private function refreshTokens(): void
    {
        $refresh = session('spotify.refresh_token');

        if (! $refresh) {
            $this->disconnect();
            throw new RuntimeException('Sessão Spotify expirada. Conecte novamente.');
        }

        $response = Http::asForm()
            ->withBasicAuth(
                (string) config('services.spotify.client_id'),
                (string) config('services.spotify.client_secret'),
            )
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh,
            ]);

        if (! $response->successful()) {
            $this->disconnect();
            throw new RuntimeException('Não foi possível renovar o token Spotify.');
        }

        $payload = $response->json();
        $payload['refresh_token'] = $payload['refresh_token'] ?? $refresh;
        $this->storeTokens($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeTokens(array $payload): void
    {
        session([
            'spotify' => [
                'access_token' => $payload['access_token'] ?? null,
                'refresh_token' => $payload['refresh_token'] ?? session('spotify.refresh_token'),
                'expires_at' => now()->timestamp + (int) ($payload['expires_in'] ?? 3600),
            ],
        ]);
    }
}
