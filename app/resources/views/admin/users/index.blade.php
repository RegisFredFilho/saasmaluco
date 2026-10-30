<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuários</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Voltar ao admin</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <p class="text-sm text-green-700">{{ session('status') }}</p>
            @endif
            <x-input-error :messages="$errors->all()" class="mt-1" />

            <form method="get" class="flex gap-2">
                <x-text-input name="q" :value="$q" placeholder="Buscar nome ou e-mail" class="w-full" />
                <x-primary-button>Buscar</x-primary-button>
            </form>

            <div class="bg-white shadow sm:rounded-lg divide-y">
                @foreach ($users as $user)
                    <div class="p-4 flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                @if ($user->isPlatformAdmin())
                                    Admin da plataforma
                                @elseif ($user->isActive())
                                    Ativo
                                @else
                                    Suspenso — {{ $user->deactivation_reason }}
                                @endif
                            </p>
                        </div>

                        <div class="space-y-2 min-w-[16rem]">
                            @if ($user->isPlatformAdmin())
                                <p class="text-xs text-gray-500">Sem ações de suspensão.</p>
                            @elseif ($user->isActive())
                                <form method="post" action="{{ route('admin.users.suspend', $user) }}" class="space-y-2">
                                    @csrf
                                    <x-text-input name="deactivation_reason" placeholder="Motivo da suspensão" class="w-full text-sm" required />
                                    <x-danger-button>Suspender</x-danger-button>
                                </form>
                            @else
                                <form method="post" action="{{ route('admin.users.reactivate', $user) }}">
                                    @csrf
                                    <x-secondary-button>Reativar</x-secondary-button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
