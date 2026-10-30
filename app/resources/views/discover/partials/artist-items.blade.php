@foreach ($artists as $artist)
    <li>
        <a href="{{ route('artists.show', $artist) }}" class="sg-panel group flex overflow-hidden transition hover:border-accent/40">
            <div class="sg-media w-28 shrink-0 sm:w-36">
                @if ($artist->coverUrl() || $artist->photoUrl())
                    <img src="{{ $artist->coverUrl() ?: $artist->photoUrl() }}" alt="" class="h-full min-h-[7rem] w-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="sg-media-overlay"></div>
                @else
                    <div class="flex min-h-[7rem] h-full items-center justify-center bg-gradient-to-br from-vibe/40 to-accent/20 font-display text-2xl font-semibold">
                        {{ strtoupper(mb_substr($artist->publicName(), 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="flex min-w-0 flex-1 flex-col justify-center p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="truncate font-semibold text-ink">{{ $artist->publicName() }}</h2>
                        <p class="mt-1 text-sm text-ink-muted">
                            {{ $artist->kind->label() }}
                            @if ($artist->city || $artist->state)
                                · {{ collect([$artist->city, $artist->state])->filter()->join('/') }}
                            @endif
                        </p>
                    </div>
                    @if ($artist->photoUrl())
                        <img src="{{ $artist->photoUrl() }}" alt="" class="hidden h-10 w-10 rounded-full object-cover ring-2 ring-canvas-line sm:block">
                    @endif
                </div>
                @if ($artist->genres->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($artist->genres->take(3) as $genre)
                            <span class="sg-badge-neutral">{{ $genre->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </a>
    </li>
@endforeach
