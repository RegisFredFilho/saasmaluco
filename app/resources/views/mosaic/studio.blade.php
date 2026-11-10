@extends('layouts.public')

@section('title', 'Studio — Semaninha')

@section('content')
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
                <a href="{{ route('mosaic.index') }}" class="sg-btn-ghost">Voltar</a>
                <form method="POST" action="{{ route('spotify.disconnect') }}">
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
        @if (session('mosaic_ready'))
            <div class="sg-panel p-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-ink-soft">Mosaico pronto para download.</p>
                <a href="{{ route('mosaic.download') }}" class="sg-btn-primary">Baixar PNG</a>
            </div>
        @endif

        @if (empty($covers))
            <div class="sg-panel p-6 text-sm text-ink-soft">
                Nenhuma capa encontrada. Ouça mais músicas no Spotify e tente de novo.
            </div>
        @else
            <form method="POST" action="{{ route('mosaic.generate') }}" class="space-y-4">
                @csrf
                <template x-for="url in selected" :key="url">
                    <input type="hidden" name="images[]" :value="url">
                </template>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach ($covers as $cover)
                        <button
                            type="button"
                            class="group relative overflow-hidden rounded-xl border border-canvas-line bg-canvas-elev text-left"
                            :class="isSelected(@js($cover['image'])) ? 'ring-2 ring-accent' : ''"
                            @click="toggle(@js($cover['image']))"
                        >
                            <img src="{{ $cover['image'] }}" alt="{{ $cover['title'] }}" class="aspect-square w-full object-cover transition group-hover:scale-105" loading="lazy">
                            <span class="absolute inset-x-0 bottom-0 bg-black/60 px-2 py-1 text-xs text-white truncate">{{ $cover['title'] }}</span>
                        </button>
                    @endforeach
                </div>

                <button type="submit" class="sg-btn-primary" :disabled="selected.length !== 9" :class="selected.length !== 9 ? 'opacity-50 pointer-events-none' : ''">
                    Gerar mosaico 3×3
                </button>
            </form>
        @endif
    </div>
@endsection
