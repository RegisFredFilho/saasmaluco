<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Desativar conta
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            A conta será desativada (soft-disable): o login será bloqueado e os perfis deixam de ser tratados como ativos.
            Os dados são retidos conforme a política do produto — não é exclusão imediata irreversível.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deactivation')"
    >Desativar conta</x-danger-button>

    <x-modal name="confirm-user-deactivation" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Confirmar desativação
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Informe sua senha para desativar a conta.
            </p>

            <div class="mt-6">
                <x-input-label for="reason" value="Motivo (opcional)" />
                <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->userDeletion->get('reason')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" value="Senha" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Senha"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Desativar
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
