<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-6">
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
                    Falta configurar as credenciais Spotify no ambiente.
                </div>
            @endunless

            <div class="flex flex-wrap gap-3 pt-2">
                @if ($connected)
                    <a href="{{ route('semaninha.studio') }}" class="sg-btn-primary">Abrir studio</a>
                    <form method="POST" action="{{ route('semaninha.spotify.disconnect') }}">
                        @csrf
                        <button type="submit" class="sg-btn-ghost">Desconectar Spotify</button>
                    </form>
                @else
                    <a href="{{ route('semaninha.spotify.connect') }}" class="sg-btn-primary">Conectar Spotify</a>
                @endif
            </div>

            <div class="rounded-xl border border-canvas-line bg-canvas px-4 py-3 text-xs text-ink-muted space-y-1">
                <p>Redirect URI no Dashboard Spotify:</p>
                <p><code class="break-all text-accent-fg">{{ config('services.spotify.redirect') }}</code></p>
            </div>
        </div>

        @if ($generations->isNotEmpty())
            <div class="sg-panel p-6 space-y-3">
                <h2 class="font-display text-lg font-semibold text-ink">Suas gerações recentes</h2>
                <ul class="space-y-2 text-sm text-ink-soft">
                    @foreach ($generations as $generation)
                        <li class="flex items-center justify-between gap-3 border-b border-canvas-line py-2 last:border-0">
                            <span>{{ $generation->created_at->format('d/m/Y H:i') }} · {{ $generation->grid_size }}×{{ $generation->grid_size }}</span>
                            <a href="{{ route('semaninha.download', $generation) }}" class="text-accent-fg hover:underline">Baixar</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>
