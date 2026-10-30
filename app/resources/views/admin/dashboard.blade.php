<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin da plataforma</h2>
            <nav class="flex gap-4 text-sm">
                <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline">Usuários</a>
                <a href="{{ route('admin.reports.index') }}" class="text-indigo-600 hover:underline">Denúncias</a>
            </nav>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-gray-600">Moderação e indicadores mínimos do MVP. Promoção a admin só via banco/tinker.</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach ([
                    'Usuários' => $stats['users'],
                    'Ativos' => $stats['active_users'],
                    'Suspensos' => $stats['suspended_users'],
                    'Denúncias abertas' => $stats['open_reports'],
                    'Reviews ocultas' => $stats['hidden_reviews'],
                    'Oportunidades abertas' => $stats['open_opportunities'],
                    'Bookings confirmados' => $stats['confirmed_bookings'],
                ] as $label => $value)
                    <div class="bg-white shadow sm:rounded-lg p-4">
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900 mb-3">Auditoria recente</h3>
                @forelse ($recentAudits as $log)
                    <div class="border-t border-gray-100 py-2 text-sm flex justify-between gap-3">
                        <div>
                            <span class="font-medium">{{ $log->action }}</span>
                            <span class="text-gray-500">por {{ $log->actor?->name ?? '—' }}</span>
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">Nenhuma ação administrativa registrada ainda.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
