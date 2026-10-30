<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Convites de banda" lead="Aceite ou recuse convites. Cada conta mantém a própria senha." />
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3">
        @if (session('status'))
            <p class="sg-badge-accent px-3 py-2 text-sm">{{ session('status') }}</p>
        @endif

        @forelse ($invites as $invite)
            <div class="sg-panel flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-ink">{{ $invite->artistProfile?->publicName() }}</p>
                    <p class="text-sm text-ink-muted">
                        Convite de {{ $invite->invitedBy?->name }} · papel {{ $invite->role->label() }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <form method="post" action="{{ route('invites.accept', $invite) }}">
                        @csrf
                        <x-primary-button>Aceitar</x-primary-button>
                    </form>
                    <form method="post" action="{{ route('invites.decline', $invite) }}">
                        @csrf
                        <x-secondary-button type="submit">Recusar</x-secondary-button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state title="Nenhum convite pendente">Quando alguém te convidar para uma banda, o pedido aparece aqui.</x-empty-state>
        @endforelse

        <div>{{ $invites->links() }}</div>
    </div>
</x-app-layout>
