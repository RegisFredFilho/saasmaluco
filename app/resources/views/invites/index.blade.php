<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Convites de banda</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <p class="text-sm text-green-700">{{ session('status') }}</p>
            @endif

            <p class="text-sm text-gray-600">Aceite ou recuse convites para integrar perfis artísticos. Cada conta mantém sua própria senha.</p>

            @forelse ($invites as $invite)
                <div class="bg-white shadow sm:rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-medium text-gray-900">{{ $invite->artistProfile?->publicName() }}</p>
                        <p class="text-sm text-gray-600">
                            Convite de {{ $invite->invitedBy?->name }} · papel {{ $invite->role->label() }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <form method="post" action="{{ route('invites.accept', $invite) }}">
                            @csrf
                            <x-primary-button>Aceitar</x-primary-button>
                        </form>
                        <form method="post" action="{{ route('invites.decline', $invite) }}">
                            @csrf
                            <x-secondary-button>Recusar</x-secondary-button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-sm text-gray-600">Nenhum convite pendente.</div>
            @endforelse

            <div>{{ $invites->links() }}</div>
        </div>
    </div>
</x-app-layout>
