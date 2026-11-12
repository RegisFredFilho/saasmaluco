<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Admin da plataforma" lead="Moderação e indicadores. Promoção a admin só via banco/tinker.">
            <x-slot name="actions">
                <a href="{{ route('admin.users.index') }}" class="sg-btn-secondary">Usuários</a>
                <a href="{{ route('admin.reports.index') }}" class="sg-btn-secondary">Denúncias</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ([
                'Usuários' => $stats['users'],
                'Ativos' => $stats['active_users'],
                'Suspensos' => $stats['suspended_users'],
                'Denúncias abertas' => $stats['open_reports'],
                'Reviews ocultas' => $stats['hidden_reviews'],
                'Oportunidades abertas' => $stats['open_opportunities'],
                'Bookings confirmados' => $stats['confirmed_bookings'],
            ] as $label => $value)
                <div class="sg-panel p-4">
                    <p class="text-xs text-ink-muted">{{ $label }}</p>
                    <p class="mt-1 font-display text-2xl font-semibold text-ink">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="sg-panel p-6">
            <h3 class="font-semibold text-ink">Auditoria recente</h3>
            @forelse ($recentAudits as $log)
                <div class="flex justify-between gap-3 border-t border-canvas-line py-3 text-sm first:mt-3 first:border-t-0">
                    <div>
                        <span class="font-medium text-ink">{{ $log->action }}</span>
                        <span class="text-ink-muted">por {{ $log->actor?->name ?? '—' }}</span>
                    </div>
                    <span class="whitespace-nowrap text-xs text-ink-muted">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                </div>
            @empty
                <p class="mt-3 text-sm text-ink-muted">Nenhuma ação administrativa registrada ainda.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
