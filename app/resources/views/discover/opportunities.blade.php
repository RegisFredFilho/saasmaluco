@extends('layouts.public')

@section('title', 'Oportunidades · SindiGig')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Oportunidades</h1>
        <p class="text-stone-600 mt-1 text-sm">Vagas abertas em estabelecimentos públicos. Localização vem do espaço.</p>
    </div>

    <form method="get" action="{{ route('opportunities.index') }}" class="bg-white border border-stone-200 rounded-lg p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="q">Título ou local</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm" placeholder="Buscar…">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="city">Cidade</label>
            <input id="city" name="city" value="{{ $filters['city'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="state">UF / estado</label>
            <input id="state" name="state" value="{{ $filters['state'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm">
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
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="event_from">Data a partir de</label>
            <input id="event_from" type="date" name="event_from" value="{{ $filters['event_from'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-stone-500 mb-1" for="event_to">Data até</label>
            <input id="event_to" type="date" name="event_to" value="{{ $filters['event_to'] ?? '' }}" class="w-full border-stone-300 rounded-md text-sm">
        </div>
        <div class="sm:col-span-2 lg:col-span-3 flex gap-3">
            <button type="submit" class="px-4 py-2 bg-stone-900 text-white text-sm rounded-md">Filtrar</button>
            <a href="{{ route('opportunities.index') }}" class="px-4 py-2 text-sm text-stone-600 hover:underline">Limpar</a>
        </div>
    </form>

    <p class="text-sm text-stone-500 mb-4">{{ $opportunities->total() }} resultado(s)</p>

    <ul class="space-y-3">
        @forelse ($opportunities as $opportunity)
            <li>
                <a href="{{ route('opportunities.show', $opportunity) }}" class="block bg-white border border-stone-200 rounded-lg p-4 hover:border-stone-400 transition-colors">
                    <h2 class="font-medium text-stone-900">{{ $opportunity->title }}</h2>
                    <p class="text-sm text-stone-600 mt-1">
                        {{ $opportunity->venueProfile->display_name }}
                        · {{ $opportunity->venueProfile->city }}/{{ $opportunity->venueProfile->state }}
                        @if ($opportunity->event_date)
                            · {{ $opportunity->event_date->format('d/m/Y') }}
                        @endif
                    </p>
                    @if ($opportunity->genres->isNotEmpty())
                        <p class="text-xs text-stone-500 mt-1 truncate">{{ $opportunity->genres->pluck('name')->join(', ') }}</p>
                    @endif
                </a>
            </li>
        @empty
            <li class="bg-white border border-stone-200 rounded-lg p-6 text-stone-600 text-sm">
                Nenhuma oportunidade aberta encontrada.
            </li>
        @endforelse
    </ul>

    <div class="mt-6">
        {{ $opportunities->links() }}
    </div>
@endsection
