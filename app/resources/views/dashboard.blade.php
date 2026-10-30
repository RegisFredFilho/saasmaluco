<x-app-layout>
    <div class="space-y-8">
        <section class="sg-hero min-h-[18rem] md:min-h-[22rem]">
            <x-stage-media variant="stage" class="absolute inset-0" />
            <div class="relative flex h-full min-h-[18rem] flex-col justify-end p-6 md:min-h-[22rem] md:p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-accent-fg">Olá, {{ Auth::user()->name }}</p>
                <h1 class="mt-2 max-w-2xl font-display text-3xl font-semibold tracking-tight text-ink md:text-5xl">
                    Conecte-se com as pessoas certas e faça sua música chegar mais longe.
                </h1>
                <p class="mt-3 max-w-xl text-sm text-ink-soft md:text-base">
                    Descubra oportunidades, acompanhe a rede e gerencie seus perfis profissionais.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('opportunities.index') }}" class="sg-btn-primary">Explorar agora</a>
                    <a href="{{ route('posts.create') }}" class="sg-btn-secondary">Nova publicação</a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <a href="{{ route('opportunities.index') }}" class="sg-panel-glass p-4 transition hover:border-accent/40">
                <p class="text-xs uppercase tracking-wider text-ink-muted">Oportunidades</p>
                <p class="mt-2 font-display text-2xl font-semibold">{{ $featuredOpportunities->count() }}+</p>
                <p class="mt-1 text-xs text-ink-muted">abertas na descoberta</p>
            </a>
            <a href="{{ route('notifications.index') }}" class="sg-panel-glass p-4 transition hover:border-accent/40">
                <p class="text-xs uppercase tracking-wider text-ink-muted">Notificações</p>
                <p class="mt-2 font-display text-2xl font-semibold">{{ $unreadNotifications }}</p>
                <p class="mt-1 text-xs text-ink-muted">não lidas</p>
            </a>
            <a href="{{ route('artist-profiles.index') }}" class="sg-panel-glass p-4 transition hover:border-accent/40">
                <p class="text-xs uppercase tracking-wider text-ink-muted">Artistas</p>
                <p class="mt-2 font-display text-2xl font-semibold">{{ $artistProfiles->count() }}</p>
                <p class="mt-1 text-xs text-ink-muted">seus perfis</p>
            </a>
            <a href="{{ route('bookings.index') }}" class="sg-panel-glass p-4 transition hover:border-accent/40">
                <p class="text-xs uppercase tracking-wider text-ink-muted">Agenda</p>
                <p class="mt-2 font-display text-2xl font-semibold">{{ $upcomingBookings->count() }}</p>
                <p class="mt-1 text-xs text-ink-muted">próximos shows</p>
            </a>
        </section>

        <section>
            <div class="mb-4 flex items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-semibold">Oportunidades em destaque</h2>
                    <p class="text-sm text-ink-muted">Gigs abertas no marketplace</p>
                </div>
                <a href="{{ route('opportunities.index') }}" class="text-sm font-medium text-accent-fg hover:underline">Ver todas</a>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($featuredOpportunities as $opportunity)
                    <a href="{{ route('opportunities.show', $opportunity) }}" class="sg-panel group overflow-hidden transition hover:border-accent/40">
                        @if ($opportunity->venueProfile?->coverUrl())
                            <div class="sg-media aspect-[16/10]">
                                <img src="{{ $opportunity->venueProfile->coverUrl() }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <div class="sg-media-overlay"></div>
                            </div>
                        @else
                            <x-media-fallback variant="opportunity" :label="$opportunity->title" class="aspect-[16/10]" />
                        @endif
                        <div class="space-y-2 p-4">
                            <span class="sg-badge-ok">Aberta</span>
                            <h3 class="line-clamp-2 font-semibold text-ink">{{ $opportunity->title }}</h3>
                            <p class="text-xs text-ink-muted">
                                {{ $opportunity->venueProfile->display_name }}
                                @if ($opportunity->event_date)
                                    · {{ $opportunity->event_date->format('d/m/Y') }}
                                @endif
                            </p>
                            <p class="text-sm font-semibold text-accent-fg">
                                @if ($opportunity->budget_min || $opportunity->budget_max)
                                    @if ($opportunity->budget_min && $opportunity->budget_max)
                                        R$ {{ number_format($opportunity->budget_min, 0, ',', '.') }} – {{ number_format($opportunity->budget_max, 0, ',', '.') }}
                                    @elseif ($opportunity->budget_min)
                                        a partir de R$ {{ number_format($opportunity->budget_min, 0, ',', '.') }}
                                    @else
                                        até R$ {{ number_format($opportunity->budget_max, 0, ',', '.') }}
                                    @endif
                                @else
                                    A combinar
                                @endif
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="sm:col-span-2 xl:col-span-4">
                        <x-empty-state title="Nenhuma oportunidade aberta no momento">
                            <a href="{{ route('venue-profiles.create') }}" class="text-accent-fg hover:underline">Publique uma vaga pelo seu espaço</a>
                        </x-empty-state>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <section class="space-y-4 lg:col-span-2">
                <div class="mb-1 flex items-end justify-between">
                    <h2 class="font-display text-xl font-semibold">Artistas em evidência</h2>
                    <a href="{{ route('artists.index') }}" class="text-sm text-accent-fg hover:underline">Explorar</a>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($spotlightArtists as $artist)
                        <a href="{{ route('artists.show', $artist) }}" class="sg-panel overflow-hidden transition hover:border-accent/40">
                            @if ($artist->coverUrl() || $artist->photoUrl())
                                <div class="sg-media aspect-square">
                                    <img src="{{ $artist->coverUrl() ?: $artist->photoUrl() }}" alt="" class="h-full w-full object-cover">
                                    <div class="sg-media-overlay"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-3">
                                        <p class="truncate font-semibold">{{ $artist->publicName() }}</p>
                                        <p class="truncate text-xs text-ink-muted">{{ $artist->kind->label() }}</p>
                                    </div>
                                </div>
                            @else
                                <x-media-fallback variant="artist" :label="$artist->publicName()" class="aspect-square" />
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-4">
                <div class="sg-panel p-5">
                    <h3 class="font-semibold">Próximos na agenda</h3>
                    <ul class="mt-3 space-y-3 text-sm">
                        @forelse ($upcomingBookings as $booking)
                            <li>
                                <a href="{{ route('bookings.show', $booking) }}" class="font-medium text-ink hover:text-accent-fg">
                                    {{ $booking->opportunity->title }}
                                </a>
                                <p class="text-xs text-ink-muted">{{ $booking->starts_at->format('d/m/Y H:i') }}</p>
                            </li>
                        @empty
                            <li class="text-ink-muted">Nenhum compromisso confirmado.</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('bookings.index') }}" class="mt-4 inline-block text-sm text-accent-fg hover:underline">Abrir agenda</a>
                </div>

                <div class="sg-panel p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold">Meus perfis</h3>
                        <a href="{{ route('artist-profiles.create') }}" class="text-xs text-accent-fg hover:underline">Novo artista</a>
                    </div>
                    <ul class="space-y-2 text-sm">
                        @forelse ($artistProfiles as $profile)
                            <li>
                                <a href="{{ route('artist-profiles.edit', $profile) }}" class="text-ink-soft hover:text-accent-fg">{{ $profile->display_name }}</a>
                            </li>
                        @empty
                            <li class="text-ink-muted">Nenhum artista ainda.</li>
                        @endforelse
                    </ul>
                    <div class="mt-4 border-t border-canvas-line pt-4">
                        <div class="mb-2 flex justify-between">
                            <span class="text-xs uppercase tracking-wider text-ink-muted">Espaços</span>
                            <a href="{{ route('venue-profiles.create') }}" class="text-xs text-accent-fg hover:underline">Novo</a>
                        </div>
                        <ul class="space-y-2 text-sm">
                            @forelse ($venueProfiles as $profile)
                                <li>
                                    <a href="{{ route('venue-profiles.edit', $profile) }}" class="text-ink-soft hover:text-accent-fg">{{ $profile->display_name }}</a>
                                </li>
                            @empty
                                <li class="text-ink-muted">Nenhum espaço ainda.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
