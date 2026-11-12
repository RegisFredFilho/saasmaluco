<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SindiGig')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:500,600,700|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="sg-shell font-sans">
    <div class="pointer-events-none fixed inset-0 bg-stage-radial opacity-80"></div>
    <header class="relative z-10 border-b border-canvas-line bg-canvas-elev/70 backdrop-blur-xl">
        <div class="mx-auto flex max-w-content flex-wrap items-center justify-between gap-3 px-4 py-4 md:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2 font-display text-xl font-semibold tracking-tight text-ink">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent text-sm font-bold text-white">S</span>
                SindiGig
            </a>
            <nav class="flex flex-wrap items-center gap-1 text-sm">
                <a href="{{ route('artists.index') }}" class="sg-btn-ghost {{ request()->routeIs('artists.*') ? 'is-active sg-chip is-active' : '' }}">Artistas</a>
                <a href="{{ route('venues.index') }}" class="sg-btn-ghost {{ request()->routeIs('venues.*') ? 'bg-accent-soft text-accent-fg' : '' }}">Espaços</a>
                <a href="{{ route('opportunities.index') }}" class="sg-btn-ghost {{ request()->routeIs('opportunities.*') ? 'bg-accent-soft text-accent-fg' : '' }}">Oportunidades</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="sg-btn-primary">Painel</a>
                @else
                    <a href="{{ route('login') }}" class="sg-btn-ghost">Entrar</a>
                    <a href="{{ route('register') }}" class="sg-btn-primary">Criar conta</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="relative z-10 mx-auto max-w-content px-4 py-8 md:px-6 md:py-10">
        @yield('content')
    </main>

    <footer class="relative z-10 border-t border-canvas-line py-8 text-center text-xs text-ink-muted">
        SindiGig — marketplace e rede profissional da música ao vivo
    </footer>
</body>
</html>
