@props([
    'followRoute',
    'unfollowRoute',
    'favoriteRoute',
    'unfavoriteRoute',
    'isFollowing' => false,
    'isFavorited' => false,
    'isOwn' => false,
    'followersCount' => 0,
    'favoritesCount' => 0,
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center gap-3']) }}>
    <p class="text-sm text-stone-300">
        <span>{{ $followersCount }} seguidor(es)</span>
        <span class="mx-1">·</span>
        <span>{{ $favoritesCount }} favorito(s)</span>
    </p>

    @auth
        @unless ($isOwn)
            <div class="flex flex-wrap gap-2">
                @if ($isFollowing)
                    <form method="post" action="{{ $unfollowRoute }}">
                        @csrf
                        @method('delete')
                        <button type="submit" class="px-3 py-1.5 text-sm border border-white/40 rounded-md hover:bg-white/10">Deixar de seguir</button>
                    </form>
                @else
                    <form method="post" action="{{ $followRoute }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-sm bg-white text-stone-950 rounded-md">Seguir</button>
                    </form>
                @endif

                @if ($isFavorited)
                    <form method="post" action="{{ $unfavoriteRoute }}">
                        @csrf
                        @method('delete')
                        <button type="submit" class="px-3 py-1.5 text-sm border border-white/40 rounded-md hover:bg-white/10">Remover favorito</button>
                    </form>
                @else
                    <form method="post" action="{{ $favoriteRoute }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-sm border border-white/40 rounded-md hover:bg-white/10">Favoritar</button>
                    </form>
                @endif
            </div>
        @endunless
    @else
        <a href="{{ route('login') }}" class="text-sm underline underline-offset-2">Entre para seguir ou favoritar</a>
    @endauth
</div>
