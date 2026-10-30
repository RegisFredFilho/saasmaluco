<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Negociações</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-600">Conversas e propostas abertas a partir de candidaturas aceitas. Só as partes autorizadas participam.</p>

            @forelse ($conversations as $conversation)
                <a href="{{ route('conversations.show', $conversation) }}" class="block bg-white shadow sm:rounded-lg p-5 hover:bg-gray-50">
                    <p class="font-medium text-gray-900">{{ $conversation->opportunity->title }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $conversation->artistProfile->publicName() }}
                        · {{ $conversation->venueProfile->display_name }}
                    </p>
                    @if ($conversation->last_message_at)
                        <p class="text-xs text-gray-500 mt-2">Última atividade {{ $conversation->last_message_at->format('d/m/Y H:i') }}</p>
                    @endif
                </a>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-sm text-gray-600">
                    Nenhuma negociação ainda. Aceite uma candidatura para abrir a conversa.
                </div>
            @endforelse

            <div>{{ $conversations->links() }}</div>
        </div>
    </div>
</x-app-layout>
