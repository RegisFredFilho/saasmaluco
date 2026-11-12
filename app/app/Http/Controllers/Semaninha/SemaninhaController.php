<?php

namespace App\Http\Controllers\Semaninha;

use App\Http\Controllers\Controller;
use App\Http\Requests\Semaninha\GenerateMosaicRequest;
use App\Models\MosaicGeneration;
use App\Services\Semaninha\MosaicImageGenerator;
use App\Services\Semaninha\SpotifyApiClient;
use App\Services\Semaninha\SpotifyAuthClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SemaninhaController extends Controller
{
    public function index(SpotifyAuthClient $auth): View
    {
        $user = auth()->user();

        return view('semaninha.index', [
            'connected' => $auth->connected($user),
            'configured' => filled(config('services.spotify.client_id'))
                && filled(config('services.spotify.client_secret')),
            'generations' => $user->mosaicGenerations()->latest()->take(5)->get(),
        ]);
    }

    public function studio(SpotifyAuthClient $auth, SpotifyApiClient $api): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $auth->connected($user)) {
            return redirect()->route('semaninha.index')->with('error', 'Conecte o Spotify primeiro.');
        }

        try {
            $covers = $api->topAlbumCovers($user, 24);
        } catch (RuntimeException $e) {
            return redirect()->route('semaninha.index')->with('error', $e->getMessage());
        }

        return view('semaninha.studio', [
            'covers' => $covers,
            'latestGeneration' => $user->mosaicGenerations()->latest()->first(),
        ]);
    }

    public function generate(
        GenerateMosaicRequest $request,
        SpotifyAuthClient $auth,
        MosaicImageGenerator $generator,
    ): RedirectResponse {
        $user = auth()->user();

        if (! $auth->connected($user)) {
            return redirect()->route('semaninha.index')->with('error', 'Conecte o Spotify primeiro.');
        }

        $grid = (int) $request->input('grid', 3);

        try {
            $relative = $generator->generate($user->id, $request->validated('images'), $grid);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $generation = MosaicGeneration::query()->create([
            'user_id' => $user->id,
            'image_path' => $relative,
            'grid_size' => $grid,
        ]);

        return redirect()
            ->route('semaninha.studio')
            ->with('status', 'Mosaico gerado.')
            ->with('generation_id', $generation->id);
    }

    public function download(MosaicGeneration $generation): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('download', $generation);

        if (! $generation->existsOnDisk()) {
            return redirect()->route('semaninha.studio')->with('error', 'Arquivo do mosaico não encontrado.');
        }

        return response()->download(
            $generation->absolutePath(),
            'semaninha-mosaic.png',
        );
    }
}
