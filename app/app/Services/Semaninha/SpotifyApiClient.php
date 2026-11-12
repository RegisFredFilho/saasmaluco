<?php

namespace App\Services\Semaninha;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpotifyApiClient
{
    public function __construct(private readonly SpotifyAuthClient $auth) {}

    /**
     * @return list<array{title: string, image: string, url: string}>
     */
    public function topAlbumCovers(User $user, int $limit = 20): array
    {
        $tracks = $this->get($user, 'me/top/tracks', [
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
            $artists = $this->get($user, 'me/top/artists', [
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
    private function get(User $user, string $path, array $query = []): array
    {
        $response = Http::withToken($this->auth->accessToken($user))
            ->get('https://api.spotify.com/v1/'.$path, $query);

        if ($response->status() === 401) {
            $connection = $user->spotifyConnection;
            if ($connection) {
                $connection->forceFill(['expires_at' => now()->subMinute()])->save();
            }

            $response = Http::withToken($this->auth->accessToken($user))
                ->get('https://api.spotify.com/v1/'.$path, $query);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Falha ao consultar a API do Spotify.');
        }

        return $response->json();
    }
}
