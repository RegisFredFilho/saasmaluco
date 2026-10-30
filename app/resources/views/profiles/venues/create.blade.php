<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo estabelecimento</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="post" action="{{ route('venue-profiles.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="display_name" value="Nome do estabelecimento" />
                        <x-text-input id="display_name" name="display_name" class="mt-1 block w-full" :value="old('display_name')" required />
                        <x-input-error :messages="$errors->get('display_name')" class="mt-2" />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_public" value="1" class="rounded border-gray-300" @checked(old('is_public', true))>
                        Perfil público
                    </label>
                    <div>
                        <x-primary-button>Criar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
