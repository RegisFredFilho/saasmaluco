<?php

namespace App\Http\Controllers;

use App\Services\MosaicImageGenerator;
use App\Services\SpotifyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MosaicController extends Controller
{
    public function index(SpotifyService $spotify): View
    {
        return view('mosaic.index', [
            'connected' => $spotify->connected(),
            'configured' => filled(config('services.spotify.client_id'))
                && filled(config('services.spotify.client_secret')),
        ]);
    }

    public function connect(SpotifyService $spotify): RedirectResponse
    {
        if (! filled(config('services.spotify.client_id')) || ! filled(config('services.spotify.client_secret'))) {
            return redirect()
                ->route('mosaic.index')
                ->with('error', 'Configure SPOTIFY_CLIENT_ID e SPOTIFY_CLIENT_SECRET no .env.');
        }

        return redirect()->away($spotify->authorizationUrl());
    }

    public function callback(Request $request, SpotifyService $spotify): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('mosaic.index')
                ->with('error', 'Autorização Spotify cancelada.');
        }

        try {
            $spotify->handleCallback(
                (string) $request->query('code'),
                $request->query('state'),
            );
        } catch (RuntimeException $e) {
            return redirect()->route('mosaic.index')->with('error', $e->getMessage());
        }

        return redirect()->route('mosaic.studio')->with('status', 'Spotify conectado.');
    }

    public function disconnect(SpotifyService $spotify): RedirectResponse
    {
        $spotify->disconnect();

        return redirect()->route('mosaic.index')->with('status', 'Spotify desconectado.');
    }

    public function studio(SpotifyService $spotify): View|RedirectResponse
    {
        if (! $spotify->connected()) {
            return redirect()->route('mosaic.index')->with('error', 'Conecte o Spotify primeiro.');
        }

        try {
            $covers = $spotify->topAlbumCovers(24);
        } catch (RuntimeException $e) {
            return redirect()->route('mosaic.index')->with('error', $e->getMessage());
        }

        return view('mosaic.studio', [
            'covers' => $covers,
        ]);
    }

    public function generate(
        Request $request,
        SpotifyService $spotify,
        MosaicImageGenerator $generator,
    ): RedirectResponse {
        if (! $spotify->connected()) {
            return redirect()->route('mosaic.index')->with('error', 'Conecte o Spotify primeiro.');
        }

        $data = $request->validate([
            'images' => ['required', 'array', 'size:9'],
            'images.*' => ['required', 'url'],
        ]);

        try {
            $relative = $generator->generate($data['images'], 3);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        session(['spotify_mosaic_path' => $relative]);

        return redirect()
            ->route('mosaic.studio')
            ->with('status', 'Mosaico gerado.')
            ->with('mosaic_ready', true);
    }

    public function download(): BinaryFileResponse|RedirectResponse
    {
        $relative = session('spotify_mosaic_path');

        if (! $relative || ! Storage::disk('public')->exists($relative)) {
            return redirect()->route('mosaic.studio')->with('error', 'Gere um mosaico antes de baixar.');
        }

        return response()->download(
            Storage::disk('public')->path($relative),
            'semaninha-mosaic.png',
        );
    }
}
