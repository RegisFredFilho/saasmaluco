@extends('layouts.public')

@section('title', 'Artistas · SindiGig')

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-canvas-line">
        <div class="relative min-h-[10rem] md:min-h-[12rem]">
            <x-stage-media variant="artist" class="absolute inset-0 opacity-50" />
            <div class="relative flex h-full min-h-[10rem] flex-col justify-end p-6 md:min-h-[12rem] md:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-accent-fg">Descoberta</p>
                <h1 class="sg-page-title mt-1">Artistas</h1>
                <p class="sg-page-lead">Encontre bandas, solos e DJs. Filtros são opcionais.</p>
            </div>
        </div>
    </div>

    <form method="get" action="{{ route('artists.index') }}" class="sg-filter-bar mb-6">
        <div>
            <label class="sg-label" for="q">Nome</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="sg-input" placeholder="Buscar…">
        </div>
        <div>
            <label class="sg-label" for="city">Cidade</label>
            <input id="city" name="city" value="{{ $filters['city'] ?? '' }}" class="sg-input">
        </div>
        <div>
            <label class="sg-label" for="state">UF</label>
            <input id="state" name="state" value="{{ $filters['state'] ?? '' }}" class="sg-input" placeholder="PR">
        </div>
        <div>
            <label class="sg-label" for="region">Região</label>
            <input id="region" name="region" value="{{ $filters['region'] ?? '' }}" class="sg-input">
        </div>
        <div>
            <label class="sg-label" for="kind">Tipo</label>
            <select id="kind" name="kind" class="sg-input">
                <option value="">Todos</option>
                @foreach ($kinds as $kind)
                    <option value="{{ $kind->value }}" @selected(($filters['kind'] ?? null) === $kind->value)>{{ $kind->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sg-label" for="presentation_type">Apresentação</label>
            <select id="presentation_type" name="presentation_type" class="sg-input">
                <option value="">Todas</option>
                @foreach ($presentationTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['presentation_type'] ?? null) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
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
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
            <button type="submit" class="sg-btn-primary">Filtrar</button>
            <a href="{{ route('artists.index') }}" class="sg-btn-ghost">Limpar</a>
        </div>
    </form>

    <div x-data="discoveryInfinite({ nextUrl: @js($artists->nextPageUrl()) })" class="space-y-4">
        <p class="text-sm text-ink-muted">{{ $artists->total() }} resultado(s)</p>
        <ul x-ref="list" class="space-y-3">
            @if ($artists->isNotEmpty())
                @include('discover.partials.artist-items', ['artists' => $artists])
            @else
                <li><x-empty-state title="Nenhum artista público">Tente limpar os filtros ou volte mais tarde.</x-empty-state></li>
            @endif
        </ul>
        <div class="flex flex-col items-center gap-3 pt-2">
            <button type="button" x-show="nextUrl" x-cloak @click="loadMore" :disabled="loading" class="sg-btn-secondary" x-text="loading ? 'Carregando…' : 'Carregar mais'"></button>
            <noscript><div class="mt-2">{{ $artists->links() }}</div></noscript>
        </div>
    </div>
@endsection
