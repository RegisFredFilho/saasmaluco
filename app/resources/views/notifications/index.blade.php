<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notificações</h2>
            <form method="post" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="text-sm text-indigo-600 hover:underline">Marcar todas como lidas</button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-3">
            <p class="text-sm text-gray-600">Alertas internos do SindiGig. E-mail/push ficam para evolução futura.</p>

            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.read', $notification) }}"
                   class="block bg-white shadow sm:rounded-lg p-4 {{ $notification->read_at ? 'opacity-70' : 'border-l-4 border-indigo-500' }}">
                    <div class="flex justify-between gap-3">
                        <p class="font-medium text-gray-900">{{ $notification->title }}</p>
                        <p class="text-xs text-gray-500 whitespace-nowrap">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">{{ $notification->body }}</p>
                </a>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-sm text-gray-600">Nenhuma notificação ainda.</div>
            @endforelse

            <div>{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>
