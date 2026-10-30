<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agenda / bookings</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-600">Compromissos confirmados. Disponibilidade informada no perfil ≠ booking.</p>

            @forelse ($bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}" class="block bg-white shadow sm:rounded-lg p-5 hover:bg-gray-50">
                    <div class="flex flex-wrap justify-between gap-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ $booking->opportunity->title }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $booking->artistProfile->publicName() }}
                                · {{ $booking->venueProfile->display_name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $booking->starts_at->format('d/m/Y H:i') }} – {{ $booking->ends_at->format('H:i') }}
                                ({{ $booking->timezone }})
                            </p>
                        </div>
                        <span class="text-sm text-gray-700">{{ $booking->status->label() }}</span>
                    </div>
                </a>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-sm text-gray-600">
                    Nenhum booking ainda. Aceite uma proposta na negociação para confirmar.
                </div>
            @endforelse

            <div>{{ $bookings->links() }}</div>
        </div>
    </div>
</x-app-layout>
