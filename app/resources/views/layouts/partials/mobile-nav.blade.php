<nav class="sg-mobile-nav" aria-label="Navegação principal">
    <div class="grid grid-cols-5 gap-1 px-2 py-2 text-[10px] font-medium text-ink-muted">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 {{ request()->routeIs('dashboard') ? 'bg-accent-soft text-accent-fg' : '' }}">
            <x-icon name="home" class="h-5 w-5" />
            Início
        </a>
        <a href="{{ route('artists.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 {{ request()->routeIs('artists.*') ? 'bg-accent-soft text-accent-fg' : '' }}">
            <x-icon name="search" class="h-5 w-5" />
            Explorar
        </a>
        <a href="{{ route('opportunities.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 {{ request()->routeIs('opportunities.*') ? 'bg-accent-soft text-accent-fg' : '' }}">
            <x-icon name="ticket" class="h-5 w-5" />
            Gigs
        </a>
        <a href="{{ route('network.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 {{ request()->routeIs('network.*') || request()->routeIs('posts.*') ? 'bg-accent-soft text-accent-fg' : '' }}">
            <x-icon name="users" class="h-5 w-5" />
            Rede
        </a>
        <a href="{{ route('conversations.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-1 py-2 {{ request()->routeIs('conversations.*') ? 'bg-accent-soft text-accent-fg' : '' }}">
            <x-icon name="message" class="h-5 w-5" />
            Chat
        </a>
    </div>
</nav>
