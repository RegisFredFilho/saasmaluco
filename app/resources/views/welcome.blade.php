<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SindiGig — marketplace musical</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:500,600,700|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sg-shell font-sans">
    <div class="relative min-h-screen overflow-hidden">
        <x-stage-media variant="stage" class="absolute inset-0" />
        <div class="absolute inset-0 bg-gradient-to-b from-canvas/40 via-canvas/75 to-canvas"></div>
        <div class="pointer-events-none absolute inset-0 bg-stage-radial"></div>

        <div class="relative mx-auto max-w-content px-4 py-6 md:px-6">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 font-display text-xl font-semibold tracking-tight text-ink">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent text-sm font-bold text-white">S</span>
                    SindiGig
                </span>
                <nav class="flex items-center gap-2 text-sm">
                    <a href="{{ route('artists.index') }}" class="sg-btn-ghost">Explorar</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="sg-btn-primary">Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="sg-btn-ghost">Entrar</a>
                        <a href="{{ route('register') }}" class="sg-btn-primary">Criar conta</a>
                    @endauth
                </nav>
            </div>

            <div class="flex min-h-[70vh] max-w-3xl flex-col justify-center pb-20 pt-16">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-accent-fg">Indústria da música ao vivo</p>
                <h1 class="mt-4 font-display text-4xl font-semibold leading-tight tracking-tight text-ink md:text-6xl">
                    O palco onde artistas e casas se encontram.
                </h1>
                <p class="mt-5 max-w-xl text-lg text-ink-soft">
                    Descubra talentos, publique oportunidades e feche shows com uma experiência feita para a cena musical.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('artists.index') }}" class="sg-btn-primary">Ver artistas</a>
                    <a href="{{ route('opportunities.index') }}" class="sg-btn-secondary">Ver oportunidades</a>
                </div>
            </div>
        </div>
    </div>

    <section class="relative mx-auto grid max-w-content grid-cols-1 gap-4 px-4 py-14 md:grid-cols-3 md:px-6">
        <a href="{{ route('artists.index') }}" class="sg-panel group overflow-hidden">
            <x-stage-media variant="artist" class="aspect-[16/9]" />
            <div class="p-5">
                <h2 class="font-display text-xl font-semibold">Artistas</h2>
                <p class="mt-2 text-sm text-ink-muted">Bandas, solos e DJs com cidade, gênero e apresentação.</p>
            </div>
        </a>
        <a href="{{ route('venues.index') }}" class="sg-panel overflow-hidden">
            <x-stage-media variant="venue" class="aspect-[16/9]" />
            <div class="p-5">
                <h2 class="font-display text-xl font-semibold">Espaços</h2>
                <p class="mt-2 text-sm text-ink-muted">Casas, bares e arenas que contratam música ao vivo.</p>
            </div>
        </a>
        <a href="{{ route('opportunities.index') }}" class="sg-panel overflow-hidden">
            <x-stage-media variant="dj" class="aspect-[16/9]" />
            <div class="p-5">
                <h2 class="font-display text-xl font-semibold">Oportunidades</h2>
                <p class="mt-2 text-sm text-ink-muted">Vagas abertas com data, local e faixa de cachê.</p>
            </div>
        </a>
    </section>
</body>
</html>
