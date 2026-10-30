<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Novo perfil de artista"
            lead="Crie o perfil profissional que aparecerá na descoberta."
        />
    </x-slot>

    <div class="mx-auto max-w-xl">
        <div class="sg-panel p-5 md:p-6">
            <form method="post" action="{{ route('artist-profiles.store') }}" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="display_name" value="Nome artístico / nome da banda" />
                    <x-text-input id="display_name" name="display_name" class="mt-1 block w-full" :value="old('display_name')" required />
                    <x-input-error :messages="$errors->get('display_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="kind" value="Tipo" />
                    <select id="kind" name="kind" class="sg-input mt-1" required>
                        @foreach ($kinds as $kind)
                            <option value="{{ $kind->value }}" @selected(old('kind') === $kind->value)>{{ $kind->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('kind')" class="mt-2" />
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-ink-soft">
                    <input type="checkbox" name="is_public" value="1" class="rounded border-canvas-line text-accent focus:ring-accent" @checked(old('is_public', true))>
                    Perfil público
                </label>
                <div>
                    <x-primary-button>Criar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
