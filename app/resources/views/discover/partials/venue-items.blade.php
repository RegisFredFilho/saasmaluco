@foreach ($venues as $venue)
    <li>
        <a href="{{ route('venues.show', $venue) }}" class="sg-panel group overflow-hidden transition hover:border-accent/40">
            @if ($venue->coverUrl())
                <div class="sg-media aspect-[21/9]">
                    <img src="{{ $venue->coverUrl() }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="sg-media-overlay"></div>
                </div>
            @else
                <x-media-fallback variant="venue" class="aspect-[21/9]" />
            @endif
            <div class="p-4">
                <h2 class="font-semibold text-ink">{{ $venue->display_name }}</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    @if ($venue->city || $venue->state)
                        {{ collect([$venue->city, $venue->state])->filter()->join('/') }}
                    @else
                        Localização não informada
                    @endif
                    @if ($venue->event_type)
                        · {{ $venue->event_type->label() }}
                    @endif
                    @if ($venue->capacity)
                        · ~{{ $venue->capacity }} pessoas
                    @endif
                </p>
                @if ($venue->genres->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($venue->genres->take(4) as $genre)
                            <span class="sg-badge-neutral">{{ $genre->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </a>
    </li>
@endforeach
