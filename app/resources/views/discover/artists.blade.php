@extends('layouts.public')

@section('title', 'Artistas · SindiGig')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Artistas</h1>
        <p class="text-stone-600 mt-1 text-sm">Perfis públicos com cidade e UF. Filtre por local, tipo ou gênero.</p>
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

    <p class="text-sm text-stone-500 mb-4">{{ $artists->total() }} resultado(s)</p>

    <ul class="space-y-3">
        @forelse ($artists as $artist)
            <li>
                <a href="{{ route('artists.show', $artist) }}" class="flex gap-4 bg-white border border-stone-200 rounded-lg p-4 hover:border-stone-400 transition-colors">
                    @if ($artist->photoUrl())
                        <img src="{{ $artist->photoUrl() }}" alt="" class="h-16 w-16 rounded-full object-cover shrink-0">
                    @else
                        <div class="h-16 w-16 rounded-full bg-stone-200 shrink-0 flex items-center justify-center font-semibold text-stone-600">
                            {{ strtoupper(mb_substr($artist->publicName(), 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h2 class="font-medium text-stone-900 truncate">{{ $artist->publicName() }}</h2>
                        <p class="text-sm text-stone-600">
                            {{ $artist->kind->label() }}
                            · {{ $artist->city }}/{{ $artist->state }}
                            @if ($artist->region)
                                · {{ $artist->region }}
                            @endif
                        </p>
                        @if ($artist->genres->isNotEmpty())
                            <p class="text-xs text-stone-500 mt-1 truncate">{{ $artist->genres->pluck('name')->join(', ') }}</p>
                        @endif
                    </div>
                </a>
            </li>
        @empty
            <li class="bg-white border border-stone-200 rounded-lg p-6 text-stone-600 text-sm">
                Nenhum artista encontrado com esses filtros.
            </li>
        @endforelse
    </ul>

    <div class="mt-6">
        {{ $artists->links() }}
    </div>
@endsection
