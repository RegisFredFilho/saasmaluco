<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->publicName() }} · SindiGig</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 text-stone-900 antialiased">
    <header class="bg-stone-950 text-white">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="font-semibold tracking-tight">SindiGig</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('artists.index') }}" class="opacity-80 hover:opacity-100">Artistas</a>
                @auth
                    @if ($canManage)
                        <a href="{{ route('artist-profiles.edit', $profile) }}" class="underline underline-offset-2">Editar perfil</a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="opacity-80 hover:opacity-100">Painel</a>
                @else
                    <a href="{{ route('login') }}" class="opacity-80 hover:opacity-100">Entrar</a>
                @endauth
            </div>
        </div>
        <div class="relative h-48 md:h-64 bg-stone-800 overflow-hidden">
            @if ($profile->coverUrl())
                <img src="{{ $profile->coverUrl() }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-80">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/90 to-transparent"></div>
        </div>
        <div class="max-w-5xl mx-auto px-4 pb-8 -mt-16 relative">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                @if ($profile->photoUrl())
                    <img src="{{ $profile->photoUrl() }}" alt="{{ $profile->publicName() }}" class="h-28 w-28 rounded-full object-cover ring-4 ring-stone-950">
                @else
                    <div class="h-28 w-28 rounded-full bg-stone-700 ring-4 ring-stone-950 flex items-center justify-center text-2xl font-semibold">
                        {{ strtoupper(mb_substr($profile->publicName(), 0, 1)) }}
                    </div>
                @endif
                <div class="pb-1">
                    <h1 class="text-3xl md:text-4xl font-semibold tracking-tight">{{ $profile->publicName() }}</h1>
                    <p class="text-stone-300 mt-1">
                        {{ $profile->kind->label() }}
                        @if ($profile->presentation_type)
                            · {{ $profile->presentation_type->label() }}
                        @endif
                        @if ($profile->city)
                            · {{ $profile->city }}@if($profile->state)/{{ $profile->state }}@endif
                        @endif
                    </p>
                    <div class="mt-3">
                        <x-network-actions
                            :follow-route="route('artists.follow', $profile)"
                            :unfollow-route="route('artists.unfollow', $profile)"
                            :favorite-route="route('artists.favorite', $profile)"
                            :unfavorite-route="route('artists.unfavorite', $profile)"
                            :is-following="$isFollowing"
                            :is-favorited="$isFavorited"
                            :is-own="$isOwn"
                            :followers-count="$profile->follows_count"
                            :favorites-count="$profile->favorites_count"
                        />
                    </div>
                </div>
            </div>
        </div>
    </header>

    @if ($errors->has('follow') || $errors->has('favorite'))
        <div class="max-w-5xl mx-auto px-4 pt-4">
            <p class="text-sm text-red-700 bg-red-50 border border-red-100 px-4 py-2 rounded">{{ $errors->first() }}</p>
        </div>
    @endif

    <main class="max-w-5xl mx-auto px-4 py-10 space-y-10">
        @if ($profile->bio)
            <section>
                <h2 class="text-lg font-semibold mb-2">Sobre</h2>
                <p class="text-stone-700 whitespace-pre-line leading-relaxed">{{ $profile->bio }}</p>
            </section>
        @endif

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-8">
                @if ($profile->genres->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-semibold mb-2">Gêneros</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($profile->genres as $genre)
                                <span class="px-3 py-1 bg-white border border-stone-200 text-sm">{{ $genre->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($profile->repertoire)
                    <div>
                        <h2 class="text-lg font-semibold mb-2">Repertório</h2>
                        <p class="text-stone-700 whitespace-pre-line">{{ $profile->repertoire }}</p>
                    </div>
                @endif

                @if ($profile->media->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-semibold mb-3">Portfólio</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach ($profile->media as $item)
                                @if ($item->type->value === 'photo')
                                    <figure class="bg-white border border-stone-200 overflow-hidden">
                                        <img src="{{ $item->url() }}" alt="{{ $item->caption }}" class="w-full h-40 object-cover">
                                        @if ($item->caption)
                                            <figcaption class="p-2 text-xs text-stone-600">{{ $item->caption }}</figcaption>
                                        @endif
                                    </figure>
                                @else
                                    <a href="{{ $item->url() }}" target="_blank" rel="noopener noreferrer"
                                       class="flex items-center justify-center h-40 bg-stone-900 text-white text-sm p-4 text-center hover:bg-stone-800">
                                        Ver vídeo{{ $item->caption ? ': '.$item->caption : '' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <h2 class="text-lg font-semibold mb-3">Experiência</h2>
                    @forelse ($profile->experiences as $experience)
                        <article class="bg-white border border-stone-200 p-4 mb-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-medium">{{ $experience->title }}</h3>
                                    <p class="text-sm text-stone-600">
                                        {{ $experience->venue_name }}
                                        @if ($experience->city) · {{ $experience->city }} @endif
                                    </p>
                                    @if ($experience->started_on)
                                        <p class="text-xs text-stone-500 mt-1">
                                            {{ $experience->started_on->format('m/Y') }}
                                            @if ($experience->ended_on)
                                                — {{ $experience->ended_on->format('m/Y') }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                @if ($experience->is_verified)
                                    <span class="text-xs px-2 py-1 bg-emerald-100 text-emerald-800">Verificada</span>
                                @else
                                    <span class="text-xs px-2 py-1 bg-stone-100 text-stone-600">Não verificada</span>
                                @endif
                            </div>
                            @if ($experience->description)
                                <p class="text-sm text-stone-700 mt-2 whitespace-pre-line">{{ $experience->description }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="text-stone-500 text-sm">Nenhuma experiência cadastrada ainda.</p>
                    @endforelse
                </div>

                <div>
                    <h2 class="text-lg font-semibold mb-2">Avaliações</h2>
                    @if ($averageRating)
                        <p class="text-sm text-stone-600 mb-3">Média {{ number_format($averageRating, 1, ',', '.') }} · {{ $reviewsCount }} avaliação(ões)</p>
                    @endif
                    <ul class="space-y-3">
                        @forelse ($profile->reviews as $review)
                            <li class="bg-white border border-stone-200 p-3 text-sm">
                                <p class="font-medium">{{ $review->rating }}/5</p>
                                @if ($review->comment)
                                    <p class="text-stone-700 mt-1 whitespace-pre-line">{{ $review->comment }}</p>
                                @endif
                                <p class="text-xs text-stone-500 mt-2">{{ $review->created_at->format('d/m/Y') }}</p>
                                @auth
                                    @can('report', $review)
                                        <form method="post" action="{{ route('reviews.report', $review) }}" class="mt-2 flex gap-2">
                                            @csrf
                                            <input type="text" name="reason" required maxlength="500" placeholder="Motivo da denúncia" class="flex-1 border-stone-300 rounded text-xs">
                                            <button class="text-xs text-red-600 hover:underline">Denunciar</button>
                                        </form>
                                    @endcan
                                    @can('hide', $review)
                                        <form method="post" action="{{ route('reviews.hide', $review) }}" class="mt-2 flex gap-2">
                                            @csrf
                                            <input type="text" name="hidden_reason" required maxlength="500" placeholder="Motivo da ocultação" class="flex-1 border-stone-300 rounded text-xs">
                                            <button class="text-xs text-red-700 hover:underline">Ocultar (admin)</button>
                                        </form>
                                    @endcan
                                @endauth
                            </li>
                        @empty
                            <li class="text-sm text-stone-500">Ainda sem avaliações de bookings concluídos.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="bg-white border border-stone-200 p-4 space-y-3 text-sm">
                    <h2 class="font-semibold text-base">Detalhes</h2>
                    @if ($profile->members_count)
                        <p><span class="text-stone-500">Integrantes:</span> {{ $profile->members_count }}</p>
                    @endif
                    @if ($profile->typical_duration_minutes)
                        <p><span class="text-stone-500">Duração habitual:</span> {{ $profile->typical_duration_minutes }} min</p>
                    @endif
                    @if ($profile->fee_min !== null || $profile->fee_max !== null)
                        <p>
                            <span class="text-stone-500">Cachê:</span>
                            @if ($profile->fee_min !== null) R$ {{ number_format((float) $profile->fee_min, 2, ',', '.') }} @endif
                            @if ($profile->fee_min !== null && $profile->fee_max !== null) — @endif
                            @if ($profile->fee_max !== null) R$ {{ number_format((float) $profile->fee_max, 2, ',', '.') }} @endif
                        </p>
                    @endif
                    @if ($profile->region)
                        <p><span class="text-stone-500">Região:</span> {{ $profile->region }}</p>
                    @endif
                    @if ($profile->own_equipment)
                        <div>
                            <p class="text-stone-500 mb-1">Equipamentos próprios</p>
                            <p class="whitespace-pre-line">{{ $profile->own_equipment }}</p>
                        </div>
                    @endif
                    @if ($profile->required_tech)
                        <div>
                            <p class="text-stone-500 mb-1">Estrutura necessária</p>
                            <p class="whitespace-pre-line">{{ $profile->required_tech }}</p>
                        </div>
                    @endif
                </div>

                @if ($profile->links->isNotEmpty())
                    <div class="bg-white border border-stone-200 p-4">
                        <h2 class="font-semibold mb-2">Links</h2>
                        <ul class="space-y-2 text-sm">
                            @foreach ($profile->links as $link)
                                <li>
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-indigo-700 hover:underline">
                                        {{ $link->label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white border border-stone-200 p-4">
                    <h2 class="font-semibold mb-2">Disponibilidade</h2>
                    @if ($profile->availability_notes)
                        <p class="text-sm text-stone-700 whitespace-pre-line mb-3">{{ $profile->availability_notes }}</p>
                    @endif
                    @forelse ($profile->availabilities as $slot)
                        <p class="text-sm {{ $slot->is_available ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $slot->date->format('d/m/Y') }} —
                            {{ $slot->is_available ? 'disponível' : 'indisponível' }}
                            @if ($slot->note) ({{ $slot->note }}) @endif
                        </p>
                    @empty
                        @unless ($profile->availability_notes)
                            <p class="text-sm text-stone-500">Sem indicação de agenda ainda.</p>
                        @endunless
                    @endforelse
                </div>
            </aside>
        </section>
    </main>
</body>
</html>
