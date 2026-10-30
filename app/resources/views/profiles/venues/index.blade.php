<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Estabelecimentos</h2>
            <a href="{{ route('venue-profiles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm rounded-md">
                Novo estabelecimento
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($profiles as $profile)
                <div class="bg-white shadow sm:rounded-lg p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-medium text-gray-900">{{ $profile->display_name }}</h3>
                        <p class="text-sm text-gray-600">
                            Completude {{ $profile->completionPercent() }}% · {{ $profile->opportunities_count }} oportunidade(s)
                            @unless ($profile->is_public) · privado @endunless
                        </p>
                    </div>
                    <div class="flex gap-4 text-sm">
                        <a href="{{ route('venues.show', $profile) }}" class="text-gray-700 hover:underline" target="_blank">Página pública</a>
                        <a href="{{ route('venue-profiles.edit', $profile) }}" class="text-indigo-600 hover:underline">Gerenciar</a>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-gray-600">
                    Você ainda não cadastrou estabelecimentos.
                    <a href="{{ route('venue-profiles.create') }}" class="text-indigo-600 hover:underline">Criar o primeiro</a>.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
