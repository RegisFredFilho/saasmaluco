<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Denúncias de avaliações" lead="Moderação de conteúdo reportado.">
            <x-slot name="actions">
                <a href="{{ route('admin.dashboard') }}" class="sg-btn-secondary">Voltar ao admin</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4">
        @if (session('status'))
            <p class="sg-alert-ok">{{ session('status') }}</p>
        @endif

        <div class="flex flex-wrap gap-2 text-sm">
            @foreach (['open' => 'Abertas', 'resolved' => 'Resolvidas', 'all' => 'Todas'] as $key => $label)
                <a href="{{ route('admin.reports.index', ['filter' => $key]) }}"
                   class="{{ $filter === $key ? 'sg-btn-primary' : 'sg-btn-secondary' }} !px-3 !py-1.5">{{ $label }}</a>
            @endforeach
        </div>

        <div class="sg-panel divide-y divide-canvas-line">
            @forelse ($reports as $report)
                <div class="space-y-3 p-4">
                    <div class="flex justify-between gap-3">
                        <div>
                            <p class="text-sm text-ink-muted">Denúncia #{{ $report->id }} · por {{ $report->reporter?->name }}</p>
                            <p class="font-medium text-ink">Motivo: {{ $report->reason }}</p>
                        </div>
                        <p class="whitespace-nowrap text-xs text-ink-muted">{{ $report->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if ($report->review)
                        <div class="rounded-lg bg-canvas p-3 text-sm">
                            <p class="text-ink">Nota {{ $report->review->rating }}/5 — {{ $report->review->author?->name }}</p>
                            <p class="mt-1 text-ink-soft">{{ $report->review->comment ?: '(sem comentário)' }}</p>
                            @if ($report->review->is_hidden)
                                <p class="mt-2 text-xs text-red-700">Oculta: {{ $report->review->hidden_reason }}</p>
                            @endif
                        </div>
                    @endif

                    @if ($report->resolved_at)
                        <p class="text-xs text-ink-muted">
                            Resolvida ({{ $report->resolution }}) em {{ $report->resolved_at->format('d/m/Y H:i') }}
                            @if ($report->resolution_note) — {{ $report->resolution_note }} @endif
                        </p>
                        @if ($report->review?->is_hidden)
                            <form method="post" action="{{ route('admin.reviews.unhide', $report->review) }}">
                                @csrf
                                <x-secondary-button type="submit">Reexibir avaliação</x-secondary-button>
                            </form>
                        @endif
                    @else
                        <div class="grid gap-3 md:grid-cols-2">
                            <form method="post" action="{{ route('admin.reports.hide', $report) }}" class="space-y-2">
                                @csrf
                                <x-text-input name="hidden_reason" placeholder="Motivo para ocultar" class="w-full text-sm" required />
                                <x-danger-button>Ocultar avaliação</x-danger-button>
                            </form>
                            <form method="post" action="{{ route('admin.reports.dismiss', $report) }}" class="space-y-2">
                                @csrf
                                <x-text-input name="resolution_note" placeholder="Motivo para arquivar" class="w-full text-sm" required />
                                <x-secondary-button type="submit">Arquivar denúncia</x-secondary-button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6">
                    <x-empty-state title="Nenhuma denúncia neste filtro" />
                </div>
            @endforelse
        </div>

        <div>{{ $reports->links() }}</div>
    </div>
</x-app-layout>
