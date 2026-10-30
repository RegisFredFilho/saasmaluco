@extends('layouts.public')

@section('title', 'Artistas · SindiGig')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Artistas</h1>
        <p class="text-stone-600 mt-1 text-sm">Listagem pública. Use os filtros se quiser refinar — não são obrigatórios.</p>
    </div>

    <form method="get" action="{{ route('artists.index') }}" class="bg-white border border-stone-200 rounded-lg p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="q">Nome</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm" placeholder="Buscar…">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="city">Cidade</label>
            <input id="city" name="city" value="{{ $filters['city'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="state">UF / estado</label>
            <input id="state" name="state" value="{{ $filters['state'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm" placeholder="PR">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="region">Região</label>
            <input id="region" name="region" value="{{ $filters['region'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="kind">Tipo</label>
            <select id="kind" name="kind" class="w-full border-stone-300 rounded-md text-sm">
                <option value="">Todos</option>
                @foreach ($kinds as $kind)
                    <option value="{{ $kind->value }}" @selected(($filters['kind'] ?? null) === $kind->value)>{{ $kind->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="presentation_type">Apresentação</label>
            <select id="presentation_type" name="presentation_type" class="w-full border-stone-300 rounded-md text-sm">
                <option value="">Todas</option>
                @foreach ($presentationTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['presentation_type'] ?? null) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="genre_id">Gênero</label>
            <select id="genre_id" name="genre_id" class="w-full border-stone-300 rounded-md text-sm">
                <option value="">Todos</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre->id }}" @selected((string) ($filters['genre_id'] ?? '') === (string) $genre->id)>{{ $genre->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2 lg:col-span-3 flex gap-3">
            <button type="submit" class="px-4 py-2 bg-stone-900 text-white text-sm rounded-md">Filtrar</button>
            <a href="{{ route('artists.index') }}" class="px-4 py-2 text-sm text-stone-600 hover:underline">Limpar</a>
        </div>
    </form>

    <div
        x-data="discoveryInfinite({ nextUrl: @js($artists->nextPageUrl()) })"
        class="space-y-4"
    >
        <p class="text-sm text-stone-500">{{ $artists->total() }} resultado(s)</p>

        <ul x-ref="list" class="space-y-3">
            @if ($artists->isNotEmpty())
                @include('discover.partials.artist-items', ['artists' => $artists])
            @else
                <li class="bg-white border border-stone-200 rounded-lg p-6 text-stone-600 text-sm">
                    Nenhum artista público cadastrado ainda.
                </li>
            @endif
        </ul>

        <div class="pt-2 flex flex-col items-center gap-3">
            <button
                type="button"
                x-show="nextUrl"
                x-cloak
                @click="loadMore"
                :disabled="loading"
                class="px-4 py-2 border border-stone-300 rounded-md text-sm text-stone-800 hover:bg-white disabled:opacity-60"
                x-text="loading ? 'Carregando…' : 'Carregar mais'"
            ></button>
            <noscript>
                <div class="mt-2">{{ $artists->links() }}</div>
            </noscript>
        </div>
    </div>
@endsection
