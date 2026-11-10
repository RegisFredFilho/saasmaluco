@extends('layouts.public')

@section('title', 'Semaninha — mosaico Spotify')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="sg-panel p-6 md:p-8 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-accent-fg">Semaninha</p>
            <h1 class="sg-page-title">Seu gosto em um mosaico</h1>
            <p class="text-ink-soft">
                Conecte o Spotify, escolha 9 capas dos álbuns mais ouvidos e baixe uma arte 1080×1080.
            </p>

            @if (session('status'))
                <div class="rounded-xl border border-accent/30 bg-accent-soft px-4 py-3 text-sm text-accent-fg">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ session('error') }}</div>
            @endif

            @unless ($configured)
                <div class="rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                    Falta o <code>SPOTIFY_CLIENT_SECRET</code> no <code>.env</code>.
                </div>
            @endunless

            <div class="flex flex-wrap gap-3 pt-2">
                @if ($connected)
                    <a href="{{ route('mosaic.studio') }}" class="sg-btn-primary">Abrir studio</a>
                    <form method="POST" action="{{ route('spotify.disconnect') }}">
                        @csrf
                        <button type="submit" class="sg-btn-ghost">Desconectar</button>
                    </form>
                @else
                    <a href="{{ route('spotify.connect') }}" class="sg-btn-primary">Conectar Spotify</a>
                @endif
            </div>

            <div class="rounded-xl border border-canvas-line bg-canvas px-4 py-3 text-xs text-ink-muted space-y-2">
                <p>1. App: <code class="text-ink-soft">{{ url('/mosaico') }}</code></p>
                <p>2. Cadastre esta Redirect URI no Spotify Dashboard:</p>
                <p><code class="break-all text-accent-fg">{{ config('services.spotify.redirect') }}</code></p>
                <p>3. O callback segue o <code>APP_URL</code> do ambiente (local/homolog/prod).</p>
            </div>
        </div>
    </div>
@endsection
