<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->display_name }} · SindiGig</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 text-stone-900 antialiased">
    <header class="bg-stone-950 text-white">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="font-semibold tracking-tight">SindiGig</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('venues.index') }}" class="opacity-80 hover:opacity-100">Estabelecimentos</a>
                @auth
                    @if ($canManage)
                        <a href="{{ route('venue-profiles.edit', $profile) }}" class="underline underline-offset-2">Gerenciar</a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="opacity-80 hover:opacity-100">Painel</a>
                @else
                    <a href="{{ route('login') }}" class="opacity-80 hover:opacity-100">Entrar</a>
                @endauth
            </div>
        </div>
        <div class="relative h-52 md:h-72 bg-stone-800 overflow-hidden">
            @if ($profile->coverUrl())
                <img src="{{ $profile->coverUrl() }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-85">
            @elseif ($profile->media->first())
                <img src="{{ $profile->media->first()->url() }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-85">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/40 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 max-w-5xl mx-auto px-4 pb-8">
                <h1 class="text-3xl md:text-4xl font-semibold tracking-tight">{{ $profile->display_name }}</h1>
                <p class="text-stone-300 mt-1">
                    @if ($profile->event_type) {{ $profile->event_type->label() }} · @endif
                    @if ($profile->neighborhood) {{ $profile->neighborhood }}, @endif
                    @if ($profile->city) {{ $profile->city }}@if($profile->state)/{{ $profile->state }}@endif @endif
                </p>
                <div class="mt-3">
                    <x-network-actions
                        :follow-route="route('venues.follow', $profile)"
                        :unfollow-route="route('venues.unfollow', $profile)"
                        :favorite-route="route('venues.favorite', $profile)"
                        :unfavorite-route="route('venues.unfavorite', $profile)"
                        :is-following="$isFollowing"
                        :is-favorited="$isFavorited"
                        :is-own="$isOwn"
                        :followers-count="$profile->follows_count"
                        :favorites-count="$profile->favorites_count"
                    />
                </div>
            </div>
        </div>
    </header>

    @if ($errors->has('follow') || $errors->has('favorite'))
        <div class="max-w-5xl mx-auto px-4 pt-4">
            <p class="text-sm text-red-700 bg-red-50 border border-red-100 px-4 py-2 rounded">{{ $errors->first() }}</p>
        </div>
    @endif

    <main class="max-w-5xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">
        <section class="md:col-span-2 space-y-8">
            @if ($profile->description)
                <div>
                    <h2 class="text-lg font-semibold mb-2">Sobre o espaço</h2>
                    <p class="text-stone-700 whitespace-pre-line leading-relaxed">{{ $profile->description }}</p>
                </div>
            @endif

            @if ($profile->genres->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold mb-2">Gêneros desejados</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($profile->genres as $genre)
                            <span class="px-3 py-1 bg-white border border-stone-200 text-sm">{{ $genre->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->media->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold mb-3">Fotos</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($profile->media as $item)
                            <figure class="bg-white border border-stone-200 overflow-hidden">
                                <img src="{{ $item->url() }}" alt="{{ $item->caption }}" class="w-full h-40 object-cover">
                                @if ($item->caption)
                                    <figcaption class="p-2 text-xs text-stone-600">{{ $item->caption }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <h2 class="text-lg font-semibold mb-3">Próximas oportunidades</h2>
                @php($openOnes = $profile->opportunities->filter(fn ($o) => $o->status === \App\Enums\OpportunityStatus::Open))
                @forelse ($openOnes as $opportunity)
                    <a href="{{ route('opportunities.show', $opportunity) }}" class="block bg-white border border-stone-200 p-4 mb-3 hover:border-stone-400">
                        <h3 class="font-medium">{{ $opportunity->title }}</h3>
                        <p class="text-sm text-stone-600">
                            @if ($opportunity->event_date) {{ $opportunity->event_date->format('d/m/Y') }} @endif
                            · {{ $opportunity->status->label() }}
                        </p>
                    </a>
                @empty
                    <p class="text-sm text-stone-500">Nenhuma oportunidade aberta no momento.</p>
                @endforelse
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">Histórico de artistas</h2>
                <p class="text-sm text-stone-500">O histórico verificado de contratações aparecerá após bookings concluídos (Etapa 9).</p>
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
        </section>

        <aside class="space-y-6">
            <div class="bg-white border border-stone-200 p-4 space-y-3 text-sm">
                <h2 class="font-semibold text-base">Informações</h2>
                @if ($profile->address_line)
                    <p><span class="text-stone-500">Endereço:</span> {{ $profile->address_line }}</p>
                @endif
                @if ($profile->capacity)
                    <p><span class="text-stone-500">Capacidade:</span> ~{{ number_format($profile->capacity, 0, ',', '.') }}</p>
                @endif
                @if ($profile->budget_min !== null || $profile->budget_max !== null)
                    <p>
                        <span class="text-stone-500">Orçamento:</span>
                        @if ($profile->budget_min !== null) R$ {{ number_format((float) $profile->budget_min, 2, ',', '.') }} @endif
                        @if ($profile->budget_min !== null && $profile->budget_max !== null) — @endif
                        @if ($profile->budget_max !== null) R$ {{ number_format((float) $profile->budget_max, 2, ',', '.') }} @endif
                    </p>
                @endif
                @if ($profile->stage_structure)
                    <div>
                        <p class="text-stone-500 mb-1">Estrutura de palco</p>
                        <p class="whitespace-pre-line">{{ $profile->stage_structure }}</p>
                    </div>
                @endif
                @if ($profile->equipment)
                    <div>
                        <p class="text-stone-500 mb-1">Equipamentos</p>
                        <p class="whitespace-pre-line">{{ $profile->equipment }}</p>
                    </div>
                @endif
                @if ($profile->technical_info)
                    <div>
                        <p class="text-stone-500 mb-1">Informações técnicas</p>
                        <p class="whitespace-pre-line">{{ $profile->technical_info }}</p>
                    </div>
                @endif
            </div>

            @if ($profile->contact_name || $profile->contact_email || $profile->contact_phone)
                <div class="bg-white border border-stone-200 p-4 text-sm space-y-2">
                    <h2 class="font-semibold text-base">Contato profissional</h2>
                    @if ($profile->contact_name)<p>{{ $profile->contact_name }}</p>@endif
                    @if ($profile->contact_email)<p><a class="text-indigo-700 hover:underline" href="mailto:{{ $profile->contact_email }}">{{ $profile->contact_email }}</a></p>@endif
                    @if ($profile->contact_phone)<p>{{ $profile->contact_phone }}</p>@endif
                    <p class="text-xs text-stone-500">Este contato é do estabelecimento — não expõe a conta pessoal do proprietário.</p>
                </div>
            @endif
        </aside>
    </main>
</body>
</html>
