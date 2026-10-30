<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SindiGig')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 text-stone-900 antialiased min-h-screen">
    <header class="bg-stone-950 text-white">
        <div class="max-w-5xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ url('/') }}" class="font-semibold tracking-tight text-lg">SindiGig</a>
            <nav class="flex flex-wrap items-center gap-4 text-sm">
                <a href="{{ route('artists.index') }}" class="{{ request()->routeIs('artists.index') ? 'underline underline-offset-2' : 'opacity-80 hover:opacity-100' }}">Artistas</a>
                <a href="{{ route('venues.index') }}" class="{{ request()->routeIs('venues.index') ? 'underline underline-offset-2' : 'opacity-80 hover:opacity-100' }}">Estabelecimentos</a>
                <a href="{{ route('opportunities.index') }}" class="{{ request()->routeIs('opportunities.index') ? 'underline underline-offset-2' : 'opacity-80 hover:opacity-100' }}">Oportunidades</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="opacity-80 hover:opacity-100">Painel</a>
                @else
                    <a href="{{ route('login') }}" class="opacity-80 hover:opacity-100">Entrar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
