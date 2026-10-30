<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Usuários" lead="Gestão de contas da plataforma.">
            <x-slot name="actions">
                <a href="{{ route('admin.dashboard') }}" class="sg-btn-secondary">Voltar ao admin</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4">
        @if (session('status'))
            <p class="sg-alert-ok">{{ session('status') }}</p>
        @endif
        <x-input-error :messages="$errors->all()" class="mt-1" />

        <form method="get" class="flex flex-col gap-2 sm:flex-row">
            <x-text-input name="q" :value="$q" placeholder="Buscar nome ou e-mail" class="w-full" />
            <x-primary-button>Buscar</x-primary-button>
        </form>

        <div class="sg-panel divide-y divide-canvas-line">
            @foreach ($users as $user)
                <div class="flex flex-col gap-3 p-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="font-medium text-ink">{{ $user->name }}</p>
                        <p class="text-sm text-ink-soft">{{ $user->email }}</p>
                        <p class="mt-1 text-xs text-ink-muted">
                            @if ($user->isPlatformAdmin())
                                <span class="sg-badge-accent">Admin da plataforma</span>
                            @elseif ($user->isActive())
                                <span class="sg-badge-neutral">Ativo</span>
                            @else
                                <span class="sg-badge-warn">Suspenso</span> — {{ $user->deactivation_reason }}
                            @endif
                        </p>
                    </div>

                    <div class="min-w-[16rem] space-y-2">
                        @if ($user->isPlatformAdmin())
                            <p class="text-xs text-ink-muted">Sem ações de suspensão.</p>
                        @elseif ($user->isActive())
                            <form method="post" action="{{ route('admin.users.suspend', $user) }}" class="space-y-2">
                                @csrf
                                <x-text-input name="deactivation_reason" placeholder="Motivo da suspensão" class="w-full text-sm" required />
                                <x-danger-button>Suspender</x-danger-button>
                            </form>
                        @else
                            <form method="post" action="{{ route('admin.users.reactivate', $user) }}">
                                @csrf
                                <x-secondary-button type="submit">Reativar</x-secondary-button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>
