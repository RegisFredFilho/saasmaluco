<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $opportunity->title }} · SindiGig</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 text-stone-900 antialiased">
    <header class="bg-white border-b border-stone-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between gap-3">
            <a href="{{ route('venues.show', $opportunity->venueProfile) }}" class="text-sm text-stone-600 hover:underline">← {{ $opportunity->venueProfile->display_name }}</a>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('opportunities.index') }}" class="text-stone-600 hover:underline">Oportunidades</a>
                <a href="{{ url('/') }}" class="font-semibold">SindiGig</a>
            </div>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-10 space-y-8">
        @if (session('status'))
            <p class="text-sm text-green-700 bg-green-50 border border-green-100 px-4 py-2 rounded">Atualizado.</p>
        @endif
        @if ($errors->any())
            <p class="text-sm text-red-700 bg-red-50 border border-red-100 px-4 py-2 rounded">{{ $errors->first() }}</p>
        @endif

        <div>
            <p class="text-sm text-stone-500">{{ $opportunity->status->label() }} · {{ $opportunity->applications_count }} candidatura(s)</p>
            <h1 class="text-3xl font-semibold tracking-tight mt-1">{{ $opportunity->title }}</h1>
            @if ($canManage)
                <a href="{{ route('venue-profiles.edit', $opportunity->venueProfile) }}" class="text-sm text-indigo-700 hover:underline">Gerenciar no painel</a>
            @endif
        </div>

        @if ($opportunity->description)
            <p class="text-stone-700 whitespace-pre-line">{{ $opportunity->description }}</p>
        @endif

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-white border border-stone-200 p-4">
            @if ($opportunity->event_date)
                <div><dt class="text-stone-500">Data</dt><dd>{{ $opportunity->event_date->format('d/m/Y') }}</dd></div>
            @endif
            @if ($opportunity->starts_at)
                <div><dt class="text-stone-500">Horário</dt><dd>{{ \Illuminate\Support\Str::of($opportunity->starts_at)->substr(0, 5) }}@if($opportunity->ends_at) – {{ \Illuminate\Support\Str::of($opportunity->ends_at)->substr(0, 5) }}@endif</dd></div>
            @endif
            @if ($opportunity->location_label)
                <div><dt class="text-stone-500">Local</dt><dd>{{ $opportunity->location_label }}</dd></div>
            @endif
            @if ($opportunity->budget_min !== null || $opportunity->budget_max !== null)
                <div>
                    <dt class="text-stone-500">Orçamento</dt>
                    <dd>
                        @if ($opportunity->budget_min !== null) R$ {{ number_format((float) $opportunity->budget_min, 2, ',', '.') }} @endif
                        @if ($opportunity->budget_min !== null && $opportunity->budget_max !== null) — @endif
                        @if ($opportunity->budget_max !== null) R$ {{ number_format((float) $opportunity->budget_max, 2, ',', '.') }} @endif
                    </dd>
                </div>
            @else
                <div><dt class="text-stone-500">Orçamento</dt><dd>A combinar</dd></div>
            @endif
            @if ($opportunity->duration_minutes)
                <div><dt class="text-stone-500">Duração</dt><dd>{{ $opportunity->duration_minutes }} min</dd></div>
            @endif
            <div><dt class="text-stone-500">Vagas</dt><dd>{{ $opportunity->slots_count }} ({{ $opportunity->accepted_applications_count }} aceita(s))</dd></div>
            @if ($opportunity->application_deadline)
                <div><dt class="text-stone-500">Prazo de candidatura</dt><dd>{{ $opportunity->application_deadline->format('d/m/Y') }}</dd></div>
            @endif
        </dl>

        @if ($opportunity->genres->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($opportunity->genres as $genre)
                    <span class="px-3 py-1 bg-white border border-stone-200 text-sm">{{ $genre->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($opportunity->available_structure)
            <div>
                <h2 class="font-semibold mb-1">Estrutura disponível</h2>
                <p class="text-sm text-stone-700 whitespace-pre-line">{{ $opportunity->available_structure }}</p>
            </div>
        @endif

        @auth
            @if ($myApplications->isNotEmpty())
                <section class="bg-white border border-stone-200 rounded-lg p-4 space-y-3">
                    <h2 class="font-semibold">Suas candidaturas</h2>
                    @foreach ($myApplications as $application)
                        <div class="border border-stone-100 rounded p-3 text-sm">
                            <p class="font-medium">{{ $application->artistProfile->publicName() }} · {{ $application->status->label() }}</p>
                            @if ($application->message)
                                <p class="text-stone-600 mt-1 whitespace-pre-line">{{ $application->message }}</p>
                            @endif
                            @if ($application->status->canBeWithdrawnByArtist())
                                <form method="post" action="{{ route('opportunities.applications.withdraw', [$opportunity, $application]) }}" class="mt-2">
                                    @csrf
                                    @method('delete')
                                    <button class="text-red-600 hover:underline text-xs">Retirar candidatura</button>
                                </form>
                            @endif
                            @if ($application->status === \App\Enums\ApplicationStatus::Accepted && $application->conversation)
                                <a href="{{ route('conversations.show', $application->conversation) }}" class="inline-block mt-2 text-indigo-700 hover:underline text-xs">Abrir negociação</a>
                            @endif
                        </div>
                    @endforeach
                </section>
            @endif

            @if ($canApply && $eligibleArtists->isNotEmpty())
                <section class="bg-white border border-stone-200 rounded-lg p-4 space-y-3">
                    <h2 class="font-semibold">Candidatar-se</h2>
                    <p class="text-xs text-stone-500">Uma candidatura por perfil artístico. Não é contratação.</p>
                    <form method="post" action="{{ route('opportunities.applications.store', $opportunity) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs text-stone-500 mb-1" for="artist_profile_id">Perfil artístico</label>
                            <select id="artist_profile_id" name="artist_profile_id" class="w-full border-stone-300 rounded-md text-sm" required>
                                @foreach ($eligibleArtists as $artist)
                                    <option value="{{ $artist->id }}">{{ $artist->publicName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-stone-500 mb-1" for="message">Mensagem (opcional)</label>
                            <textarea id="message" name="message" rows="3" class="w-full border-stone-300 rounded-md text-sm" maxlength="4000"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-stone-900 text-white text-sm rounded-md">Enviar candidatura</button>
                    </form>
                </section>
            @elseif ($opportunity->status->acceptsApplications() && auth()->check() && $eligibleArtists->isEmpty() && $myApplications->isEmpty())
                <p class="text-sm text-stone-600">
                    Você precisa de um perfil artístico gerenciável para se candidatar.
                    <a href="{{ route('artist-profiles.create') }}" class="text-indigo-700 hover:underline">Criar perfil</a>
                </p>
            @elseif (! $opportunity->acceptsNewApplications() && $opportunity->status->value === 'open')
                <p class="text-sm text-stone-600">Candidaturas encerradas (prazo ou vagas preenchidas).</p>
            @endif
        @else
            @if ($opportunity->acceptsNewApplications())
                <p class="text-sm text-stone-600">
                    <a href="{{ route('login') }}" class="underline">Entre</a> com um perfil artístico para se candidatar.
                </p>
            @endif
        @endauth

        @if ($canManage)
            <section class="bg-white border border-stone-200 rounded-lg p-4 space-y-4">
                <h2 class="font-semibold">Candidatos ({{ $applications->count() }})</h2>
                @forelse ($applications as $application)
                    <div class="border border-stone-100 rounded p-3 space-y-2 text-sm">
                        <div class="flex flex-wrap justify-between gap-2">
                            <div>
                                <a href="{{ route('artists.show', $application->artistProfile) }}" class="font-medium text-indigo-700 hover:underline" target="_blank">
                                    {{ $application->artistProfile->publicName() }}
                                </a>
                                <p class="text-stone-500">{{ $application->status->label() }} · enviada {{ $application->created_at->format('d/m/Y H:i') }}</p>
                                @if ($application->conversation)
                                    <a href="{{ route('conversations.show', $application->conversation) }}" class="text-indigo-700 hover:underline text-xs">Negociação</a>
                                @endif
                            </div>
                        </div>
                        @if ($application->message)
                            <p class="text-stone-700 whitespace-pre-line">{{ $application->message }}</p>
                        @endif
                        @php($allowed = array_merge([$application->status], $application->status->allowedVenueTransitions()))
                        <form method="post" action="{{ route('opportunities.applications.status', [$opportunity, $application]) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            @csrf
                            @method('patch')
                            <select name="status" class="border-stone-300 rounded-md text-sm">
                                @foreach ($allowed as $status)
                                    <option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="venue_notes" value="{{ $application->venue_notes }}" placeholder="Nota interna (opcional)" class="border-stone-300 rounded-md text-sm">
                            <button class="px-3 py-2 bg-stone-900 text-white text-xs rounded-md">Atualizar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-stone-500">Nenhuma candidatura ainda.</p>
                @endforelse
            </section>
        @endif
    </main>
</body>
</html>
