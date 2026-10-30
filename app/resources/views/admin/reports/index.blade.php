<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Denúncias de avaliações</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Voltar ao admin</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <p class="text-sm text-green-700">{{ session('status') }}</p>
            @endif

            <div class="flex gap-3 text-sm">
                <a href="{{ route('admin.reports.index', ['filter' => 'open']) }}"
                   class="{{ $filter === 'open' ? 'font-semibold text-indigo-700' : 'text-gray-600 hover:underline' }}">Abertas</a>
                <a href="{{ route('admin.reports.index', ['filter' => 'resolved']) }}"
                   class="{{ $filter === 'resolved' ? 'font-semibold text-indigo-700' : 'text-gray-600 hover:underline' }}">Resolvidas</a>
                <a href="{{ route('admin.reports.index', ['filter' => 'all']) }}"
                   class="{{ $filter === 'all' ? 'font-semibold text-indigo-700' : 'text-gray-600 hover:underline' }}">Todas</a>
            </div>

            <div class="bg-white shadow sm:rounded-lg divide-y">
                @forelse ($reports as $report)
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between gap-3">
                            <div>
                                <p class="text-sm text-gray-500">Denúncia #{{ $report->id }} · por {{ $report->reporter?->name }}</p>
                                <p class="font-medium text-gray-900">Motivo: {{ $report->reason }}</p>
                            </div>
                            <p class="text-xs text-gray-500 whitespace-nowrap">{{ $report->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        @if ($report->review)
                            <div class="rounded bg-gray-50 p-3 text-sm">
                                <p class="text-gray-800">Nota {{ $report->review->rating }}/5 — {{ $report->review->author?->name }}</p>
                                <p class="text-gray-600 mt-1">{{ $report->review->comment ?: '(sem comentário)' }}</p>
                                @if ($report->review->is_hidden)
                                    <p class="text-xs text-red-700 mt-2">Oculta: {{ $report->review->hidden_reason }}</p>
                                @endif
                            </div>
                        @endif

                        @if ($report->resolved_at)
                            <p class="text-xs text-gray-500">
                                Resolvida ({{ $report->resolution }}) em {{ $report->resolved_at->format('d/m/Y H:i') }}
                                @if ($report->resolution_note) — {{ $report->resolution_note }} @endif
                            </p>
                            @if ($report->review?->is_hidden)
                                <form method="post" action="{{ route('admin.reviews.unhide', $report->review) }}">
                                    @csrf
                                    <x-secondary-button>Reexibir avaliação</x-secondary-button>
                                </form>
                            @endif
                        @else
                            <div class="grid md:grid-cols-2 gap-3">
                                <form method="post" action="{{ route('admin.reports.hide', $report) }}" class="space-y-2">
                                    @csrf
                                    <x-text-input name="hidden_reason" placeholder="Motivo para ocultar" class="w-full text-sm" required />
                                    <x-danger-button>Ocultar avaliação</x-danger-button>
                                </form>
                                <form method="post" action="{{ route('admin.reports.dismiss', $report) }}" class="space-y-2">
                                    @csrf
                                    <x-text-input name="resolution_note" placeholder="Motivo para arquivar" class="w-full text-sm" required />
                                    <x-secondary-button>Arquivar denúncia</x-secondary-button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-600">Nenhuma denúncia neste filtro.</div>
                @endforelse
            </div>

            <div>{{ $reports->links() }}</div>
        </div>
    </div>
</x-app-layout>
