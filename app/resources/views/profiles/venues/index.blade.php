<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Meus estabelecimentos" lead="Espaços que você possui ou gerencia.">
            <x-slot name="actions">
                <a href="{{ route('venue-profiles.create') }}" class="sg-btn-primary">Novo estabelecimento</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="space-y-3">
        @forelse ($profiles as $profile)
            <div class="sg-panel flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-semibold text-ink">{{ $profile->display_name }}</h3>
                    <p class="text-sm text-ink-muted">
                        Completude {{ $profile->completionPercent() }}% · {{ $profile->opportunities_count }} oportunidade(s)
                        @unless ($profile->is_public) · <span class="sg-badge-neutral">privado</span> @endunless
                    </p>
                </div>
                <div class="flex gap-3 text-sm">
                    <a href="{{ route('venues.show', $profile) }}" class="sg-btn-ghost" target="_blank">Página pública</a>
                    <a href="{{ route('venue-profiles.edit', $profile) }}" class="sg-btn-secondary">Gerenciar</a>
                </div>
            </div>
        @empty
            <x-empty-state title="Nenhum estabelecimento">
                <a href="{{ route('venue-profiles.create') }}" class="font-medium text-accent-fg hover:underline">Criar o primeiro</a>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
