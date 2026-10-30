<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Minha rede</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <p class="text-sm text-gray-600">
                Seguir e favoritar não são contratação. Contagens vêm dos vínculos reais no banco.
            </p>

            <section class="bg-white shadow sm:rounded-lg p-6 space-y-3">
                <h3 class="font-medium text-gray-900">Oportunidades de espaços que você segue</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($feed as $opportunity)
                        <li>
                            <a href="{{ route('opportunities.show', $opportunity) }}" class="text-indigo-600 hover:underline">
                                {{ $opportunity->title }}
                            </a>
                            <span class="text-gray-500">
                                · {{ $opportunity->venueProfile->display_name }}
                                @if ($opportunity->event_date)
                                    · {{ $opportunity->event_date->format('d/m/Y') }}
                                @endif
                            </span>
                        </li>
                    @empty
                        <li class="text-gray-500">Nenhuma oportunidade aberta nos espaços seguidos. Siga estabelecimentos para montar este feed.</li>
                    @endforelse
                </ul>
            </section>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <section class="bg-white shadow sm:rounded-lg p-6 space-y-3">
                    <h3 class="font-medium text-gray-900">Artistas que sigo ({{ $followedArtists->count() }})</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse ($followedArtists as $follow)
                            @continue(! $follow->followable)
                            <li>
                                <a href="{{ route('artists.show', $follow->followable) }}" class="text-indigo-600 hover:underline">
                                    {{ $follow->followable->publicName() }}
                                </a>
                            </li>
                        @empty
                            <li class="text-gray-500">Nenhum ainda.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="bg-white shadow sm:rounded-lg p-6 space-y-3">
                    <h3 class="font-medium text-gray-900">Espaços que sigo ({{ $followedVenues->count() }})</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse ($followedVenues as $follow)
                            @continue(! $follow->followable)
                            <li>
                                <a href="{{ route('venues.show', $follow->followable) }}" class="text-indigo-600 hover:underline">
                                    {{ $follow->followable->display_name }}
                                </a>
                            </li>
                        @empty
                            <li class="text-gray-500">Nenhum ainda.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="bg-white shadow sm:rounded-lg p-6 space-y-3">
                    <h3 class="font-medium text-gray-900">Artistas favoritos ({{ $favoriteArtists->count() }})</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse ($favoriteArtists as $favorite)
                            @continue(! $favorite->favoritable)
                            <li>
                                <a href="{{ route('artists.show', $favorite->favoritable) }}" class="text-indigo-600 hover:underline">
                                    {{ $favorite->favoritable->publicName() }}
                                </a>
                            </li>
                        @empty
                            <li class="text-gray-500">Nenhum ainda.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="bg-white shadow sm:rounded-lg p-6 space-y-3">
                    <h3 class="font-medium text-gray-900">Espaços favoritos ({{ $favoriteVenues->count() }})</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse ($favoriteVenues as $favorite)
                            @continue(! $favorite->favoritable)
                            <li>
                                <a href="{{ route('venues.show', $favorite->favoritable) }}" class="text-indigo-600 hover:underline">
                                    {{ $favorite->favoritable->display_name }}
                                </a>
                            </li>
                        @empty
                            <li class="text-gray-500">Nenhum ainda.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
