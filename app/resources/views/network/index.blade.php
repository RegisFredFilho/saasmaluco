<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Rede profissional"
            lead="Feed de quem você segue — publicações e oportunidades."
        >
            <x-slot name="actions">
                <a href="{{ route('posts.create') }}" class="sg-btn-primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    Publicar
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="space-y-4 lg:col-span-2">
            <a href="{{ route('posts.create') }}" class="sg-panel flex items-center gap-3 p-4 transition hover:border-accent/40">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-accent to-vibe text-sm font-semibold text-white">
                    {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="flex-1 rounded-xl border border-canvas-line bg-canvas-mist px-4 py-2.5 text-sm text-ink-muted">
                    Compartilhe um ensaio, show ou novidade…
                </span>
                <span class="sg-btn-secondary !py-2">Abrir</span>
            </a>

            <ul class="space-y-4">
                @forelse ($feedItems as $entry)
                    @if ($entry['type'] === 'opportunity')
                        @php($opportunity = $entry['item'])
                        <li class="sg-panel overflow-hidden">
                            <div class="border-b border-amber-500/20 bg-amber-500/10 px-4 py-2">
                                <span class="sg-badge-warn">Oportunidade</span>
                            </div>
                            <div class="p-4">
                                <a href="{{ route('opportunities.show', $opportunity) }}" class="font-display text-lg font-semibold text-ink hover:text-accent-fg">
                                    {{ $opportunity->title }}
                                </a>
                                <p class="mt-1 text-sm text-ink-muted">
                                    {{ $opportunity->venueProfile->display_name }}
                                    @if ($opportunity->event_date)
                                        · {{ $opportunity->event_date->format('d/m/Y') }}
                                    @endif
                                </p>
                                <a href="{{ route('opportunities.show', $opportunity) }}" class="sg-btn-primary mt-4 !py-2">Ver oportunidade</a>
                            </div>
                        </li>
                    @else
                        @php($post = $entry['item'])
                        <li class="sg-panel p-4 md:p-5">
                            <span class="sg-badge-neutral mb-3">Publicação</span>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-accent-soft text-sm font-semibold text-accent-fg">
                                    {{ strtoupper(mb_substr($post->publisherLabel(), 0, 1)) }}
                                </span>
                                <div>
                                    @if ($post->publisherUrl())
                                        <a href="{{ $post->publisherUrl() }}" class="text-sm font-semibold text-ink hover:text-accent-fg">
                                            {{ $post->publisherLabel() }}
                                        </a>
                                    @else
                                        <span class="text-sm font-semibold text-ink">{{ $post->publisherLabel() }}</span>
                                    @endif
                                    <p class="text-xs text-ink-muted">{{ $post->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-ink-soft md:text-base">{{ $post->body }}</p>
                        </li>
                    @endif
                @empty
                    <li>
                        <x-empty-state title="Feed vazio">
                            Siga artistas e estabelecimentos, ou
                            <a href="{{ route('posts.create') }}" class="font-medium text-accent-fg hover:underline">publique algo</a>.
                        </x-empty-state>
                    </li>
                @endforelse
            </ul>
        </section>

        <aside class="space-y-4">
            <div class="sg-panel overflow-hidden">
                <x-stage-media variant="studio" class="aspect-[16/9] opacity-70" />
                <div class="p-5">
                    <h3 class="font-semibold">Amplie sua rede</h3>
                    <p class="mt-2 text-sm text-ink-muted">Descubra artistas e espaços para seguir.</p>
                    <a href="{{ route('artists.index') }}" class="sg-btn-secondary mt-4 w-full">Explorar</a>
                </div>
            </div>

            <div class="sg-panel p-5">
                <h3 class="font-semibold">Artistas que sigo ({{ $followedArtists->count() }})</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($followedArtists as $follow)
                        @continue(! $follow->followable)
                        <li>
                            <a href="{{ route('artists.show', $follow->followable) }}" class="text-ink-soft hover:text-accent-fg">
                                {{ $follow->followable->publicName() }}
                            </a>
                        </li>
                    @empty
                        <li class="text-ink-muted">Nenhum ainda.</li>
                    @endforelse
                </ul>
            </div>
            <div class="sg-panel p-5">
                <h3 class="font-semibold">Espaços que sigo ({{ $followedVenues->count() }})</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($followedVenues as $follow)
                        @continue(! $follow->followable)
                        <li>
                            <a href="{{ route('venues.show', $follow->followable) }}" class="text-ink-soft hover:text-accent-fg">
                                {{ $follow->followable->display_name }}
                            </a>
                        </li>
                    @empty
                        <li class="text-ink-muted">Nenhum ainda.</li>
                    @endforelse
                </ul>
            </div>
            <div class="sg-panel p-5">
                <h3 class="font-semibold">Favoritos</h3>
                <p class="mt-1 text-xs text-ink-muted">Artistas {{ $favoriteArtists->count() }} · Espaços {{ $favoriteVenues->count() }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($favoriteArtists->take(5) as $favorite)
                        @continue(! $favorite->favoritable)
                        <li>
                            <a href="{{ route('artists.show', $favorite->favoritable) }}" class="text-ink-soft hover:text-accent-fg">
                                {{ $favorite->favoritable->publicName() }}
                            </a>
                        </li>
                    @endforeach
                    @foreach ($favoriteVenues->take(5) as $favorite)
                        @continue(! $favorite->favoritable)
                        <li>
                            <a href="{{ route('venues.show', $favorite->favoritable) }}" class="text-ink-soft hover:text-accent-fg">
                                {{ $favorite->favoritable->display_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
</x-app-layout>
