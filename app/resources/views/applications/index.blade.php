<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Candidaturas"
            lead="Candidatura ≠ contratação. O aceite libera a negociação."
        >
            <x-slot name="actions">
                <a href="{{ route('opportunities.index') }}" class="sg-btn-secondary">Explorar oportunidades</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-3">
        @forelse ($applications as $application)
            <div class="sg-panel p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <a href="{{ route('opportunities.show', $application->opportunity) }}" class="font-semibold text-accent-fg hover:underline">
                            {{ $application->opportunity->title }}
                        </a>
                        <p class="mt-1 text-sm text-ink-muted">
                            {{ $application->artistProfile->publicName() }}
                            · <span class="sg-badge-neutral">{{ $application->status->label() }}</span>
                            · {{ $application->opportunity->venueProfile->display_name }}
                        </p>
                    </div>
                    <p class="text-xs text-ink-muted">{{ $application->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        @empty
            <x-empty-state title="Nenhuma candidatura">
                <a href="{{ route('opportunities.index') }}" class="font-medium text-accent-fg hover:underline">Explorar oportunidades</a>
            </x-empty-state>
        @endforelse
        <div>{{ $applications->links() }}</div>
    </div>
</x-app-layout>
