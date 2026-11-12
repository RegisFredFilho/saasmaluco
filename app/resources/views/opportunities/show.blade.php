<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $opportunity->title }} · SindiGig</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:500,600,700|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sg-shell font-sans">
    <header class="border-b border-canvas-line bg-canvas-elev">
        <div class="mx-auto flex max-w-3xl items-center justify-between gap-3 px-4 py-4 md:px-6">
            <a href="{{ route('venues.show', $opportunity->venueProfile) }}" class="text-sm text-ink-muted hover:text-accent-fg">
                ← {{ $opportunity->venueProfile->display_name }}
            </a>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('opportunities.index') }}" class="sg-btn-ghost">Oportunidades</a>
                <a href="{{ url('/') }}" class="font-display font-semibold text-ink">SindiGig</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-3xl space-y-6 px-4 py-8 md:px-6 md:py-10">
        @if (session('status'))
            <p class="sg-alert-ok">Atualizado.</p>
        @endif
        @if ($errors->any())
            <p class="sg-alert-err">{{ $errors->first() }}</p>
        @endif

        <div class="sg-panel overflow-hidden">
            @if ($opportunity->venueProfile?->coverUrl())
                <div class="sg-media aspect-[21/9]">
                    <img src="{{ $opportunity->venueProfile->coverUrl() }}" alt="" class="h-full w-full object-cover">
                    <div class="sg-media-overlay"></div>
                </div>
            @else
                <x-stage-media variant="crowd" class="aspect-[21/9] opacity-70" />
            @endif
            <div class="p-5 md:p-6">
            <div class="flex flex-wrap items-center gap-2 text-sm text-ink-muted">
                <span class="sg-badge-accent">{{ $opportunity->status->label() }}</span>
                <span>{{ $opportunity->applications_count }} candidatura(s)</span>
            </div>
            <h1 class="sg-page-title mt-3">{{ $opportunity->title }}</h1>
            @if ($canManage)
                <a href="{{ route('venue-profiles.edit', $opportunity->venueProfile) }}" class="mt-2 inline-block text-sm font-medium text-accent-fg hover:underline">Gerenciar no painel</a>
            @endif

            @if ($opportunity->description)
                <p class="mt-4 whitespace-pre-line text-ink-soft">{{ $opportunity->description }}</p>
            @endif
            </div>
        </div>

        <dl class="sg-panel grid grid-cols-1 gap-4 p-5 text-sm sm:grid-cols-2 md:p-6">
            @if ($opportunity->event_date)
                <div>
                    <dt class="text-ink-muted">Data</dt>
                    <dd class="mt-0.5 font-medium">{{ $opportunity->event_date->format('d/m/Y') }}</dd>
                </div>
            @endif
            @if ($opportunity->starts_at)
                <div>
                    <dt class="text-ink-muted">Horário</dt>
                    <dd class="mt-0.5 font-medium">{{ \Illuminate\Support\Str::of($opportunity->starts_at)->substr(0, 5) }}@if($opportunity->ends_at) – {{ \Illuminate\Support\Str::of($opportunity->ends_at)->substr(0, 5) }}@endif</dd>
                </div>
            @endif
            @if ($opportunity->location_label)
                <div>
                    <dt class="text-ink-muted">Local</dt>
                    <dd class="mt-0.5 font-medium">{{ $opportunity->location_label }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-ink-muted">Orçamento</dt>
                <dd class="mt-0.5 font-medium">
                    @if ($opportunity->budget_min !== null || $opportunity->budget_max !== null)
                        @if ($opportunity->budget_min !== null) R$ {{ number_format((float) $opportunity->budget_min, 2, ',', '.') }} @endif
                        @if ($opportunity->budget_min !== null && $opportunity->budget_max !== null) — @endif
                        @if ($opportunity->budget_max !== null) R$ {{ number_format((float) $opportunity->budget_max, 2, ',', '.') }} @endif
                    @else
                        A combinar
                    @endif
                </dd>
            </div>
            @if ($opportunity->duration_minutes)
                <div>
                    <dt class="text-ink-muted">Duração</dt>
                    <dd class="mt-0.5 font-medium">{{ $opportunity->duration_minutes }} min</dd>
                </div>
            @endif
            <div>
                <dt class="text-ink-muted">Vagas</dt>
                <dd class="mt-0.5 font-medium">{{ $opportunity->slots_count }} ({{ $opportunity->accepted_applications_count }} aceita(s))</dd>
            </div>
            @if ($opportunity->application_deadline)
                <div>
                    <dt class="text-ink-muted">Prazo de candidatura</dt>
                    <dd class="mt-0.5 font-medium">{{ $opportunity->application_deadline->format('d/m/Y') }}</dd>
                </div>
            @endif
        </dl>

        @if ($opportunity->genres->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($opportunity->genres as $genre)
                    <span class="sg-badge-neutral">{{ $genre->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($opportunity->available_structure)
            <div class="sg-panel p-5 md:p-6">
                <h2 class="font-semibold text-ink">Estrutura disponível</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-ink-soft">{{ $opportunity->available_structure }}</p>
            </div>
        @endif

        @auth
            @if ($myApplications->isNotEmpty())
                <section class="sg-panel space-y-3 p-5 md:p-6">
                    <h2 class="font-semibold text-ink">Suas candidaturas</h2>
                    @foreach ($myApplications as $application)
                        <div class="rounded-lg border border-canvas-line p-4 text-sm">
                            <p class="font-medium">{{ $application->artistProfile->publicName() }} · <span class="sg-badge-neutral">{{ $application->status->label() }}</span></p>
                            @if ($application->message)
                                <p class="mt-1 whitespace-pre-line text-ink-muted">{{ $application->message }}</p>
                            @endif
                            @if ($application->status->canBeWithdrawnByArtist())
                                <form method="post" action="{{ route('opportunities.applications.withdraw', [$opportunity, $application]) }}" class="mt-2">
                                    @csrf
                                    @method('delete')
                                    <button class="text-xs font-medium text-red-700 hover:underline">Retirar candidatura</button>
                                </form>
                            @endif
                            @if ($application->status === \App\Enums\ApplicationStatus::Accepted && $application->conversation)
                                <a href="{{ route('conversations.show', $application->conversation) }}" class="mt-2 inline-block text-xs font-medium text-accent-fg hover:underline">Abrir negociação</a>
                            @endif
                        </div>
                    @endforeach
                </section>
            @endif

            @if ($canApply && $eligibleArtists->isNotEmpty())
                <section class="sg-panel space-y-3 p-5 md:p-6">
                    <h2 class="font-semibold text-ink">Candidatar-se</h2>
                    <p class="text-xs text-ink-muted">Uma candidatura por perfil artístico. Não é contratação.</p>
                    <form method="post" action="{{ route('opportunities.applications.store', $opportunity) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="sg-label" for="artist_profile_id">Perfil artístico</label>
                            <select id="artist_profile_id" name="artist_profile_id" class="sg-input" required>
                                @foreach ($eligibleArtists as $artist)
                                    <option value="{{ $artist->id }}">{{ $artist->publicName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="sg-label" for="message">Mensagem (opcional)</label>
                            <textarea id="message" name="message" rows="3" class="sg-input" maxlength="4000"></textarea>
                        </div>
                        <button type="submit" class="sg-btn-primary">Enviar candidatura</button>
                    </form>
                </section>
            @elseif ($opportunity->status->acceptsApplications() && auth()->check() && $eligibleArtists->isEmpty() && $myApplications->isEmpty())
                <p class="text-sm text-ink-muted">
                    Você precisa de um perfil artístico gerenciável para se candidatar.
                    <a href="{{ route('artist-profiles.create') }}" class="font-medium text-accent-fg hover:underline">Criar perfil</a>
                </p>
            @elseif (! $opportunity->acceptsNewApplications() && $opportunity->status->value === 'open')
                <p class="text-sm text-ink-muted">Candidaturas encerradas (prazo ou vagas preenchidas).</p>
            @endif
        @else
            @if ($opportunity->acceptsNewApplications())
                <p class="text-sm text-ink-muted">
                    <a href="{{ route('login') }}" class="font-medium text-accent-fg hover:underline">Entre</a> com um perfil artístico para se candidatar.
                </p>
            @endif
        @endauth

        @if ($canManage)
            <section class="sg-panel space-y-4 p-5 md:p-6">
                <h2 class="font-semibold text-ink">Candidatos ({{ $applications->count() }})</h2>
                @forelse ($applications as $application)
                    <div class="space-y-2 rounded-lg border border-canvas-line p-4 text-sm">
                        <div class="flex flex-wrap justify-between gap-2">
                            <div>
                                <a href="{{ route('artists.show', $application->artistProfile) }}" class="font-medium text-accent-fg hover:underline" target="_blank">
                                    {{ $application->artistProfile->publicName() }}
                                </a>
                                <p class="text-ink-muted">{{ $application->status->label() }} · enviada {{ $application->created_at->format('d/m/Y H:i') }}</p>
                                @if ($application->conversation)
                                    <a href="{{ route('conversations.show', $application->conversation) }}" class="text-xs font-medium text-accent-fg hover:underline">Negociação</a>
                                @endif
                            </div>
                        </div>
                        @if ($application->message)
                            <p class="whitespace-pre-line text-ink-soft">{{ $application->message }}</p>
                        @endif
                        @php($allowed = array_merge([$application->status], $application->status->allowedVenueTransitions()))
                        <form method="post" action="{{ route('opportunities.applications.status', [$opportunity, $application]) }}" class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                            @csrf
                            @method('patch')
                            <select name="status" class="sg-input">
                                @foreach ($allowed as $status)
                                    <option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="venue_notes" value="{{ $application->venue_notes }}" placeholder="Nota interna (opcional)" class="sg-input">
                            <button class="sg-btn-primary">Atualizar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-ink-muted">Nenhuma candidatura ainda.</p>
                @endforelse
            </section>
        @endif
    </main>
</body>
</html>
