<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SindiGig</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 text-stone-900 antialiased min-h-screen">
    <header class="bg-stone-950 text-white">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <span class="font-semibold tracking-tight text-lg">SindiGig</span>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('artists.index') }}" class="opacity-80 hover:opacity-100">Explorar</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="opacity-80 hover:opacity-100">Painel</a>
                @else
                    <a href="{{ route('login') }}" class="opacity-80 hover:opacity-100">Entrar</a>
                    <a href="{{ route('register') }}" class="underline underline-offset-2">Criar conta</a>
                @endauth
            </nav>
        </div>
        <div class="max-w-5xl mx-auto px-4 pb-16 pt-10">
            <h1 class="text-4xl md:text-5xl font-semibold tracking-tight max-w-xl">
                SindiGig
            </h1>
            <p class="mt-4 text-stone-300 max-w-lg text-lg">
                Conecte artistas e casas de show. Descubra perfis e oportunidades perto de você.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('artists.index') }}" class="inline-flex px-5 py-2.5 bg-white text-stone-950 text-sm font-medium rounded-md">
                    Ver artistas
                </a>
                <a href="{{ route('opportunities.index') }}" class="inline-flex px-5 py-2.5 border border-white/40 text-white text-sm rounded-md hover:bg-white/10">
                    Ver oportunidades
                </a>
            </div>
        </div>
    </header>

    <section class="max-w-5xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('artists.index') }}" class="block bg-white border border-stone-200 rounded-lg p-6 hover:border-stone-400 transition-colors">
            <h2 class="font-medium text-lg">Artistas</h2>
            <p class="text-sm text-stone-600 mt-2">Bandas, solos e DJs com cidade, gênero e apresentação.</p>
        </a>
        <a href="{{ route('venues.index') }}" class="block bg-white border border-stone-200 rounded-lg p-6 hover:border-stone-400 transition-colors">
            <h2 class="font-medium text-lg">Estabelecimentos</h2>
            <p class="text-sm text-stone-600 mt-2">Espaços públicos por cidade, tipo de evento e gênero.</p>
        </a>
        <a href="{{ route('opportunities.index') }}" class="block bg-white border border-stone-200 rounded-lg p-6 hover:border-stone-400 transition-colors">
            <h2 class="font-medium text-lg">Oportunidades</h2>
            <p class="text-sm text-stone-600 mt-2">Vagas abertas filtráveis por local e data.</p>
        </a>
    </section>
</body>
</html>
