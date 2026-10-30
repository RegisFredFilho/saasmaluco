<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Painel
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-800 space-y-2">
                <p>Olá, {{ Auth::user()->name }}. Gerencie sua conta e seus perfis profissionais.</p>
                <p class="text-sm">
                    <a href="{{ route('artists.index') }}" class="text-indigo-600 hover:underline">Explorar artistas</a>
                    ·
                    <a href="{{ route('venues.index') }}" class="text-indigo-600 hover:underline">estabelecimentos</a>
                    ·
                    <a href="{{ route('opportunities.index') }}" class="text-indigo-600 hover:underline">oportunidades</a>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-medium text-gray-900">Artistas</h3>
                        <a href="{{ route('artist-profiles.create') }}" class="text-sm text-indigo-600 hover:underline">Novo</a>
                    </div>
                    <ul class="space-y-2 text-sm text-gray-700">
                        @forelse ($artistProfiles as $profile)
                            <li>
                                <a href="{{ route('artist-profiles.edit', $profile) }}" class="hover:underline">
                                    {{ $profile->display_name }}
                                </a>
                            </li>
                        @empty
                            <li class="text-gray-500">Nenhum perfil ainda.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-medium text-gray-900">Estabelecimentos</h3>
                        <a href="{{ route('venue-profiles.create') }}" class="text-sm text-indigo-600 hover:underline">Novo</a>
                    </div>
                    <ul class="space-y-2 text-sm text-gray-700">
                        @forelse ($venueProfiles as $profile)
                            <li>
                                <a href="{{ route('venue-profiles.edit', $profile) }}" class="hover:underline">
                                    {{ $profile->display_name }}
                                </a>
                            </li>
                        @empty
                            <li class="text-gray-500">Nenhum estabelecimento ainda.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
