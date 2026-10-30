<section>
    <header>
        <h2 class="text-lg font-medium text-ink">
            Informações da conta
        </h2>

        <p class="mt-1 text-sm text-ink-muted">
            Atualize seus dados básicos, localização, foto e preferências.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            @if ($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="Foto de perfil" class="h-16 w-16 rounded-full object-cover">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-canvas text-sm text-ink-muted">
                    Sem foto
                </div>
            @endif
            <div class="flex-1">
                <x-input-label for="avatar" value="Foto de perfil" />
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-ink-muted">
                <p class="mt-1 text-xs text-ink-muted">JPEG, PNG ou WebP — máx. 2 MB</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" value="Nome" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="city" value="Cidade" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>
            <div>
                <x-input-label for="state" value="Estado" />
                <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->state)" />
                <x-input-error class="mt-2" :messages="$errors->get('state')" />
            </div>
            <div>
                <x-input-label for="country" value="País" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $user->country)" />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="locale" value="Idioma" />
                <select id="locale" name="locale" class="sg-input mt-1">
                    @php($locale = old('locale', data_get($user->preferences, 'locale', 'pt_BR')))
                    <option value="pt_BR" @selected($locale === 'pt_BR')>Português (Brasil)</option>
                    <option value="en" @selected($locale === 'en')>English</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('locale')" />
            </div>
            <div class="flex items-end pb-2">
                <label class="inline-flex items-center gap-2 text-sm text-ink-soft">
                    <input type="checkbox" name="notify_email" value="1" class="rounded border-canvas-line text-accent shadow-sm focus:ring-accent"
                        @checked(old('notify_email', data_get($user->preferences, 'notify_email', true)))>
                    Receber e-mails de notificação
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Salvar</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-ink-muted">
                    Salvo.
                </p>
            @endif
        </div>
    </form>
</section>
