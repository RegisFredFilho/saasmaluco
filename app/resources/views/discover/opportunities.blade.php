@extends('layouts.public')

@section('title', 'Oportunidades · SindiGig')

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-canvas-line">
        <div class="relative min-h-[10rem]">
            <x-stage-media variant="crowd" class="absolute inset-0 opacity-45" />
            <div class="relative flex min-h-[10rem] flex-col justify-end p-6 md:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-accent-fg">Marketplace</p>
                <h1 class="sg-page-title mt-1">Oportunidades</h1>
                <p class="sg-page-lead">Vagas abertas em espaços públicos. Filtros opcionais.</p>
            </div>
        </div>
    </div>

    <form method="get" action="{{ route('opportunities.index') }}" class="sg-filter-bar mb-6">
        <div>
            <label class="sg-label" for="q">Título ou local</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="sg-input" placeholder="Buscar…">
        </div>
        <div>
            <label class="sg-label" for="city">Cidade</label>
            <input id="city" name="city" value="{{ $filters['city'] ?? '' }}" class="sg-input">
        </div>
        <div>
            <label class="sg-label" for="state">UF</label>
            <input id="state" name="state" value="{{ $filters['state'] ?? '' }}" class="sg-input">
        </div>
        <div>
            <label class="sg-label" for="genre_id">Gênero</label>
            <select id="genre_id" name="genre_id" class="sg-input">
                <option value="">Todos</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre->id }}" @selected((string) ($filters['genre_id'] ?? '') === (string) $genre->id)>{{ $genre->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sg-label" for="event_from">Data a partir de</label>
            <input id="event_from" type="date" name="event_from" value="{{ $filters['event_from'] ?? '' }}" class="sg-input">
        </div>
        <div>
            <label class="sg-label" for="event_to">Data até</label>
            <input id="event_to" type="date" name="event_to" value="{{ $filters['event_to'] ?? '' }}" class="sg-input">
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
            <button type="submit" class="sg-btn-primary">Filtrar</button>
            <a href="{{ route('opportunities.index') }}" class="sg-btn-ghost">Limpar</a>
        </div>
    </form>

    <div x-data="discoveryInfinite({ nextUrl: @js($opportunities->nextPageUrl()) })" class="space-y-4">
        <p class="text-sm text-ink-muted">{{ $opportunities->total() }} resultado(s)</p>
        <ul x-ref="list" class="space-y-3">
            @if ($opportunities->isNotEmpty())
                @include('discover.partials.opportunity-items', ['opportunities' => $opportunities])
            @else
                <li><x-empty-state title="Nenhuma oportunidade aberta">Volte em breve ou ajuste os filtros.</x-empty-state></li>
            @endif
        </ul>
        <div class="flex flex-col items-center gap-3 pt-2">
            <button type="button" x-show="nextUrl" x-cloak @click="loadMore" :disabled="loading" class="sg-btn-secondary" x-text="loading ? 'Carregando…' : 'Carregar mais'"></button>
            <noscript><div class="mt-2">{{ $opportunities->links() }}</div></noscript>
        </div>
    </div>
@endsection
