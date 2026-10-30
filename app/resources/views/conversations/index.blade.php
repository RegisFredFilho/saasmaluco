<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Negociações"
            lead="Conversas comerciais — separadas das mensagens sociais."
        />
    </x-slot>

    <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-[18rem_1fr]">
        <aside class="sg-panel h-fit p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-muted">Canais</p>
            <div class="mt-3 space-y-1">
                <div class="sg-nav-link is-active">
                    <x-icon name="briefcase" class="h-4 w-4" />
                    Comercial
                </div>
                <div class="sg-nav-link opacity-50" title="Em breve">
                    <x-icon name="message" class="h-4 w-4" />
                    Social
                    <span class="ms-auto text-[10px] uppercase text-ink-muted">em breve</span>
                </div>
            </div>
        </aside>

        <div class="space-y-3">
            @forelse ($conversations as $conversation)
                <a href="{{ route('conversations.show', $conversation) }}" class="sg-panel flex items-start gap-4 p-4 transition hover:border-accent/40">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-accent-soft font-semibold text-accent-fg">
                        {{ strtoupper(mb_substr($conversation->artistProfile->publicName(), 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="truncate font-semibold text-ink">{{ $conversation->opportunity->title }}</p>
                            @if ($conversation->last_message_at)
                                <span class="text-xs text-ink-muted">{{ $conversation->last_message_at->format('d/m H:i') }}</span>
                            @endif
                        </div>
                        <p class="mt-1 truncate text-sm text-ink-muted">
                            {{ $conversation->artistProfile->publicName() }}
                            · {{ $conversation->venueProfile->display_name }}
                        </p>
                        <span class="sg-badge-vibe mt-2">Comercial</span>
                    </div>
                </a>
            @empty
                <x-empty-state title="Nenhuma negociação">Aceite uma candidatura para abrir a conversa.</x-empty-state>
            @endforelse
            <div>{{ $conversations->links() }}</div>
        </div>
    </div>
</x-app-layout>
