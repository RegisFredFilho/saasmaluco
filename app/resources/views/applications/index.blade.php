<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Minhas candidaturas</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-600">Candidatura ≠ contratação. Aceite pelo estabelecimento libera o fluxo de negociação (próximas etapas).</p>

            @forelse ($applications as $application)
                <div class="bg-white shadow sm:rounded-lg p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        <div>
                            <a href="{{ route('opportunities.show', $application->opportunity) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $application->opportunity->title }}
                            </a>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $application->artistProfile->publicName() }}
                                · {{ $application->status->label() }}
                                · {{ $application->opportunity->venueProfile->display_name }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-500">{{ $application->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-gray-600 text-sm">
                    Nenhuma candidatura ainda.
                    <a href="{{ route('opportunities.index') }}" class="text-indigo-600 hover:underline">Explorar oportunidades</a>
                </div>
            @endforelse

            <div>{{ $applications->links() }}</div>
        </div>
    </div>
</x-app-layout>
