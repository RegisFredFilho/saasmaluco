<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Notificações" lead="Alertas internos do SindiGig.">
            <x-slot name="actions">
                <form method="post" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="sg-btn-secondary">Marcar todas como lidas</button>
                </form>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3">
        @forelse ($notifications as $notification)
            <a href="{{ route('notifications.read', $notification) }}"
               class="sg-list-row {{ $notification->read_at ? 'opacity-70' : 'border-l-4 border-l-accent' }}">
                <div class="flex justify-between gap-3">
                    <p class="font-semibold text-ink">{{ $notification->title }}</p>
                    <p class="whitespace-nowrap text-xs text-ink-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <p class="mt-1 text-sm text-ink-muted">{{ $notification->body }}</p>
            </a>
        @empty
            <x-empty-state title="Nenhuma notificação">Quando houver atividade relevante, ela aparece aqui.</x-empty-state>
        @endforelse
        <div>{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
