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

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-center']) }}>
    <p class="text-sm text-ink-muted">
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
                        <button type="submit" class="sg-btn-secondary !py-1.5">Deixar de seguir</button>
                    </form>
                @else
                    <form method="post" action="{{ $followRoute }}">
                        @csrf
                        <button type="submit" class="sg-btn-primary !py-1.5">Seguir</button>
                    </form>
                @endif

                @if ($isFavorited)
                    <form method="post" action="{{ $unfavoriteRoute }}">
                        @csrf
                        @method('delete')
                        <button type="submit" class="sg-btn-ghost !py-1.5">Remover favorito</button>
                    </form>
                @else
                    <form method="post" action="{{ $favoriteRoute }}">
                        @csrf
                        <button type="submit" class="sg-btn-secondary !py-1.5">Favoritar</button>
                    </form>
                @endif
            </div>
        @endunless
    @else
        <a href="{{ route('login') }}" class="text-sm text-accent-fg underline underline-offset-2">Entre para seguir ou favoritar</a>
    @endauth
</div>
