@foreach ($opportunities as $opportunity)
    <li>
        <a href="{{ route('opportunities.show', $opportunity) }}" class="sg-panel group flex flex-col overflow-hidden transition hover:border-accent/40 sm:flex-row">
            <div class="sg-media w-full shrink-0 sm:w-44">
                @if ($opportunity->venueProfile?->coverUrl())
                    <img src="{{ $opportunity->venueProfile->coverUrl() }}" alt="" class="h-36 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-full">
                    <div class="sg-media-overlay"></div>
                @else
                    <div class="flex h-36 items-center justify-center bg-gradient-to-br from-amber-500/20 via-canvas-elev to-accent/20 sm:h-full">
                        <x-icon name="ticket" class="h-8 w-8 text-ink/40" />
                    </div>
                @endif
            </div>
            <div class="flex min-w-0 flex-1 flex-col justify-between gap-3 p-4 sm:flex-row sm:items-start">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap gap-2">
                        <span class="sg-badge-ok">Aberta</span>
                        @if ($opportunity->genres->isNotEmpty())
                            <span class="sg-badge-neutral">{{ $opportunity->genres->first()->name }}</span>
                        @endif
                    </div>
                    <h2 class="font-semibold text-ink">{{ $opportunity->title }}</h2>
                    <p class="mt-1 text-sm text-ink-muted">
                        {{ $opportunity->venueProfile->display_name }}
                        @if ($opportunity->venueProfile->city || $opportunity->venueProfile->state)
                            · {{ collect([$opportunity->venueProfile->city, $opportunity->venueProfile->state])->filter()->join('/') }}
                        @endif
                        @if ($opportunity->event_date)
                            · {{ $opportunity->event_date->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                <div class="shrink-0 text-left sm:text-right">
                    @if ($opportunity->budget_min || $opportunity->budget_max)
                        <p class="text-sm font-semibold text-accent-fg">
                            @if ($opportunity->budget_min && $opportunity->budget_max)
                                R$ {{ number_format($opportunity->budget_min, 0, ',', '.') }} – {{ number_format($opportunity->budget_max, 0, ',', '.') }}
                            @elseif ($opportunity->budget_min)
                                a partir de R$ {{ number_format($opportunity->budget_min, 0, ',', '.') }}
                            @else
                                até R$ {{ number_format($opportunity->budget_max, 0, ',', '.') }}
                            @endif
                        </p>
                    @else
                        <p class="text-sm text-ink-muted">A combinar</p>
                    @endif
                    <p class="mt-2 text-xs font-medium text-ink-soft group-hover:text-accent-fg">Ver detalhes →</p>
                </div>
            </div>
        </a>
    </li>
@endforeach
