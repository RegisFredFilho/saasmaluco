<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Meus artistas" lead="Perfis profissionais que você possui ou administra.">
            <x-slot name="actions">
                <a href="{{ route('artist-profiles.create') }}" class="sg-btn-primary">Novo perfil</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="space-y-3">
        @forelse ($profiles as $profile)
            <div class="sg-panel flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-semibold text-ink">{{ $profile->publicName() }}</h3>
                    <p class="text-sm text-ink-muted">
                        {{ $profile->kind->label() }} · completude {{ $profile->completionPercent() }}%
                        @if (! $profile->is_public)
                            · <span class="sg-badge-neutral">privado</span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-3 text-sm">
                    <a href="{{ route('artists.show', $profile) }}" class="sg-btn-ghost" target="_blank">Página pública</a>
                    <a href="{{ route('artist-profiles.edit', $profile) }}" class="sg-btn-secondary">Editar</a>
                </div>
            </div>
        @empty
            <x-empty-state title="Nenhum perfil de artista">
                <a href="{{ route('artist-profiles.create') }}" class="font-medium text-accent-fg hover:underline">Criar o primeiro</a>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
