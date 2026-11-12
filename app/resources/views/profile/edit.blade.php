<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Minha conta"
            lead="Dados de acesso e segurança da conta."
        />
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="sg-panel p-5 md:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="sg-panel p-5 md:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="sg-panel p-5 md:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
