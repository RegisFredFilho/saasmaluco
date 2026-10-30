@php
    $unread = $unreadNotificationsCount ?? 0;
    $forceExpanded = $forceExpanded ?? false;
    $labelShow = $forceExpanded ? 'true' : '!collapsed';
@endphp

<aside
    class="sg-sidebar relative"
    @unless ($forceExpanded)
        :class="collapsed ? 'is-collapsed' : ''"
    @endunless
>
    <div class="flex h-14 items-center gap-2 border-b border-canvas-line px-3">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-2 overflow-hidden">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent text-sm font-bold text-white shadow-glow">S</span>
            <span class="truncate font-display text-lg font-semibold tracking-tight text-ink" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>SindiGig</span>
        </a>
        @unless ($forceExpanded)
            <button type="button" class="sg-btn-ghost !p-2" @click="toggleCollapsed()" :aria-label="collapsed ? 'Expandir menu' : 'Recolher menu'">
                <x-icon name="chevron-left" class="h-4 w-4 transition" x-bind:class="collapsed ? 'rotate-180' : ''" />
            </button>
        @endunless
    </div>

    <nav class="flex-1 overflow-y-auto px-2 pb-4">
        <p class="sg-nav-section" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Principal</p>
        <a href="{{ route('dashboard') }}" class="sg-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" title="Início">
            <x-icon name="home" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Início</span>
        </a>
        <a href="{{ route('artists.index') }}" class="sg-nav-link {{ request()->routeIs('artists.*') && ! request()->routeIs('artist-profiles.*') ? 'is-active' : '' }}" title="Explorar">
            <x-icon name="search" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Explorar</span>
        </a>
        <a href="{{ route('opportunities.index') }}" class="sg-nav-link {{ request()->routeIs('opportunities.*') ? 'is-active' : '' }}" title="Oportunidades">
            <x-icon name="ticket" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Oportunidades</span>
        </a>
        <a href="{{ route('venues.index') }}" class="sg-nav-link {{ request()->routeIs('venues.*') && ! request()->routeIs('venue-profiles.*') ? 'is-active' : '' }}" title="Espaços">
            <x-icon name="building" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Espaços</span>
        </a>

        <p class="sg-nav-section" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Rede</p>
        <a href="{{ route('network.index') }}" class="sg-nav-link {{ request()->routeIs('network.*') || request()->routeIs('posts.*') ? 'is-active' : '' }}" title="Rede">
            <x-icon name="users" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Rede profissional</span>
        </a>

        <p class="sg-nav-section" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Marketplace</p>
        <a href="{{ route('applications.index') }}" class="sg-nav-link {{ request()->routeIs('applications.*') ? 'is-active' : '' }}" title="Candidaturas">
            <x-icon name="briefcase" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Candidaturas</span>
        </a>
        <a href="{{ route('conversations.index') }}" class="sg-nav-link {{ request()->routeIs('conversations.*') ? 'is-active' : '' }}" title="Negociações">
            <x-icon name="message" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Negociações</span>
        </a>
        <a href="{{ route('bookings.index') }}" class="sg-nav-link {{ request()->routeIs('bookings.*') ? 'is-active' : '' }}" title="Agenda">
            <x-icon name="calendar" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Agenda</span>
        </a>

        <p class="sg-nav-section" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Comunicação</p>
        <a href="{{ route('notifications.index') }}" class="sg-nav-link relative {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}" title="Notificações">
            <x-icon name="bell" class="h-5 w-5 shrink-0" />
            <span class="flex flex-1 items-center justify-between gap-2" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>
                <span>Notificações</span>
                @if ($unread > 0)
                    <span class="sg-badge-accent">{{ $unread > 99 ? '99+' : $unread }}</span>
                @endif
            </span>
            @if ($unread > 0 && ! $forceExpanded)
                <span class="absolute end-2 top-2 h-2 w-2 rounded-full bg-accent" x-show="collapsed" x-cloak></span>
            @endif
        </a>
        <a href="{{ route('invites.index') }}" class="sg-nav-link {{ request()->routeIs('invites.*') ? 'is-active' : '' }}" title="Convites">
            <x-icon name="spark" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Convites</span>
        </a>

        <p class="sg-nav-section" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Minha área</p>
        <a href="{{ route('artist-profiles.index') }}" class="sg-nav-link {{ request()->routeIs('artist-profiles.*') ? 'is-active' : '' }}" title="Meus artistas">
            <x-icon name="mic" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Meus artistas</span>
        </a>
        <a href="{{ route('venue-profiles.index') }}" class="sg-nav-link {{ request()->routeIs('venue-profiles.*') ? 'is-active' : '' }}" title="Meus espaços">
            <x-icon name="building" class="h-5 w-5 shrink-0" />
            <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Meus espaços</span>
        </a>
    </nav>

    <div class="border-t border-canvas-line p-2">
        <a href="{{ route('profile.edit') }}" class="sg-nav-link" title="Conta">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-soft text-xs font-semibold text-accent-fg">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </span>
            <span class="min-w-0 flex-1" x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>
                <span class="block truncate text-sm font-medium text-ink">{{ Auth::user()->name }}</span>
                <span class="block truncate text-xs text-ink-muted">Conta</span>
            </span>
        </a>
        @if (Auth::user()->isPlatformAdmin())
            <a href="{{ route('admin.dashboard') }}" class="sg-nav-link mt-1" title="Admin">
                <x-icon name="settings" class="h-5 w-5 shrink-0" />
                <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Admin</span>
            </a>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="sg-nav-link w-full text-left text-red-400 hover:bg-red-500/100/10 hover:text-red-300" title="Sair">
                <x-icon name="logout" class="h-5 w-5 shrink-0" />
                <span x-show="{{ $labelShow }}" @unless($forceExpanded) x-cloak @endunless>Sair</span>
            </button>
        </form>
    </div>
</aside>
