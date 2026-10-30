<header class="sg-topbar">
    <button
        type="button"
        class="sg-btn-ghost -ms-2 md:hidden"
        @click="sidebarOpen = !sidebarOpen"
        aria-label="Abrir menu"
    >
        <x-icon name="menu" class="h-5 w-5" />
    </button>

    <form action="{{ route('artists.index') }}" method="get" class="hidden min-w-0 flex-1 sm:block md:max-w-lg">
        <label class="sr-only" for="global-q">Buscar</label>
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" />
            <input
                id="global-q"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Buscar artistas, espaços, gigs…"
                class="sg-input !pl-10"
            >
        </div>
    </form>

    <div class="ms-auto flex items-center gap-1">
        <a href="{{ route('posts.create') }}" class="sg-btn-primary hidden sm:inline-flex">
            <x-icon name="plus" class="h-4 w-4" />
            Publicar
        </a>
        <a href="{{ route('notifications.index') }}" class="sg-btn-ghost relative" title="Notificações">
            <x-icon name="bell" class="h-5 w-5" />
            @if (($unreadNotificationsCount ?? 0) > 0)
                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-accent shadow-glow"></span>
            @endif
        </a>
        <a href="{{ route('profile.edit') }}" class="sg-btn-ghost hidden sm:inline-flex" title="Conta">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-accent to-vibe text-xs font-semibold text-white">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </span>
        </a>
    </div>
</header>
