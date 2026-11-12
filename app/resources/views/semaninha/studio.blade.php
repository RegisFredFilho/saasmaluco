<x-app-layout>
    <div class="space-y-6" x-data="{
        selected: [],
        toggle(url) {
            if (this.selected.includes(url)) {
                this.selected = this.selected.filter(u => u !== url);
                return;
            }
            if (this.selected.length >= 9) return;
            this.selected.push(url);
        },
        isSelected(url) { return this.selected.includes(url); }
    }">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-accent-fg">Semaninha</p>
                <h1 class="sg-page-title">Escolha 9 capas</h1>
                <p class="text-sm text-ink-soft">Selecionadas: <span x-text="selected.length"></span>/9</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('semaninha.index') }}" class="sg-btn-ghost">Voltar</a>
                <form method="POST" action="{{ route('semaninha.spotify.disconnect') }}">
                    @csrf
                    <button class="sg-btn-ghost" type="submit">Desconectar</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-accent/30 bg-accent-soft px-4 py-3 text-sm text-accent-fg">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ session('error') }}</div>
        @endif
        @if (session('generation_id'))
            <div class="sg-panel p-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-ink-soft">Mosaico pronto para download.</p>
                <a href="{{ route('semaninha.download', session('generation_id')) }}" class="sg-btn-primary">Baixar PNG</a>
            </div>
        @elseif ($latestGeneration)
            <div class="sg-panel p-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-ink-soft">Último mosaico: {{ $latestGeneration->created_at->format('d/m/Y H:i') }}</p>
                <a href="{{ route('semaninha.download', $latestGeneration) }}" class="sg-btn-secondary">Baixar de novo</a>
            </div>
        @endif

        @if (empty($covers))
            <div class="sg-panel p-6 text-sm text-ink-soft">
                Nenhuma capa encontrada. Ouça mais músicas no Spotify e tente de novo.
            </div>
        @else
            <form method="POST" action="{{ route('semaninha.generate') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="grid" value="3">
                <template x-for="url in selected" :key="url">
                    <input type="hidden" name="images[]" :value="url">
                </template>

                @include('semaninha.partials.cover-grid', ['covers' => $covers])

                <button type="submit" class="sg-btn-primary" :disabled="selected.length !== 9" :class="selected.length !== 9 ? 'opacity-50 pointer-events-none' : ''">
                    Gerar mosaico 3×3
                </button>
            </form>
        @endif
    </div>
</x-app-layout>
