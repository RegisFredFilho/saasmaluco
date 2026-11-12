<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nova publicação" lead="Publique como sua conta ou como um perfil que você administra." />
    </x-slot>

    <div class="mx-auto max-w-2xl">
        @php
            $defaultKey = old('publisher_key');
            if (! $defaultKey) {
                if (request('publisher_type') === 'artist' && request('publisher_id')) {
                    $defaultKey = 'artist:'.request('publisher_id');
                } elseif (request('publisher_type') === 'venue' && request('publisher_id')) {
                    $defaultKey = 'venue:'.request('publisher_id');
                } else {
                    $defaultKey = 'user';
                }
            }
        @endphp

        <form method="post" action="{{ route('posts.store') }}" class="sg-panel space-y-4 p-6">
            @csrf

            <div>
                <x-input-label for="publisher_key" value="Publicar como" />
                <select id="publisher_key" name="publisher_key" class="sg-input" required>
                    <option value="user" @selected($defaultKey === 'user')>Minha conta ({{ auth()->user()->name }})</option>
                    @foreach ($artists as $artist)
                        <option value="artist:{{ $artist->id }}" @selected($defaultKey === 'artist:'.$artist->id)>
                            Artista: {{ $artist->display_name }}
                        </option>
                    @endforeach
                    @foreach ($venues as $venue)
                        <option value="venue:{{ $venue->id }}" @selected($defaultKey === 'venue:'.$venue->id)>
                            Espaço: {{ $venue->display_name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('publisher_key')" class="mt-2" />
                <x-input-error :messages="$errors->get('publisher_type')" class="mt-2" />
                <x-input-error :messages="$errors->get('publisher_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="body" value="Texto" />
                <textarea id="body" name="body" rows="5" class="sg-input" required maxlength="5000">{{ old('body') }}</textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-ink-soft">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" value="1" class="rounded border-canvas-line text-accent focus:ring-accent" @checked(old('is_public', '1') == '1')>
                Pública (perfil e feed de quem segue)
            </label>

            <div class="flex gap-3 pt-2">
                <x-primary-button>Publicar</x-primary-button>
                <a href="{{ url()->previous() }}" class="sg-btn-ghost self-center">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
